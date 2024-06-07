<?php

namespace App\Controller;

use App\Entity\Employee;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/employee', name: 'api_employee')]
class EmployeeController extends AbstractController
{
    public function __construct(EntityManagerInterface $em, SerializerInterface $serializer)
    {
        $this->em = $em;
        $this->serializer = $serializer;
    }

    #[Route('/', name: 'create_employees', methods:"POST")]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $file = $request->files->get('file');
        if (!$file) {
            return new JsonResponse(['error' => 'No file uploaded'], 400);
        }

        $fileContent = file_get_contents($file->getPathname());
        $csvData = array_map('str_getcsv', explode("\n", $fileContent));
        $header = array_shift($csvData);

        $skippedEmployees = array();
        $employeeIds = array();

        foreach ($csvData as $row) {
            if (count($row) === 1 && empty($row[0])) {
                continue;
            }

            if (count($header) !== count($row)) {
                continue;
            }

            $employeeData = array_combine($header, $row);

            if (!is_numeric($employeeData['Emp ID'])) {
                $skippedEmployees[] = ['Error at [Emp ID] employee'=> $employeeData];
                continue;
            }

            try {
                $dateOfBirth = new \DateTime($employeeData['Date of Birth']);
            } catch (\Exception $e) {
                $skippedEmployees[] = ['Error at [Date of Birth] employee'=> $employeeData];
                continue;
            }

            try {
                $timeOfBirth = new \DateTime($employeeData['Time of Birth']);
            } catch (\Exception $e) {
                $skippedEmployees[] = ['Error at [Time of Birth] employee'=> $employeeData];
                continue;
            }

            try {
                $dateOfJoining = new \DateTime($employeeData['Date of Joining']);
            } catch (\Exception $e) {
                $skippedEmployees[] = ['Error at [Date of Joining] employee'=> $employeeData];
                continue;
            }

            $employeeId = $employeeData['Emp ID'] ?? null;

            if ($employeeId !== null) {
            $existingEmployee = $this->em->getRepository(Employee::class)->findOneBy(['employeeID' => $employeeId]);
            if ($existingEmployee) {
                $skippedEmployees[] = ['Duplicate Employee ID in DB: '=> $employeeId];
                continue;
            }

                if (in_array($employeeId, $employeeIds)) {
                    $skippedEmployees[] = ['Duplicate Employee ID in CSV: '=> $employeeId];
                    continue;
                }
                $employeeIds[] = $employeeId;
            }

            $employee = new Employee();
            $employee->setEmployeeID($employeeData['Emp ID']);
            $employee->setUserName($employeeData['User Name']);
            $employee->setNamePrefix($employeeData['Name Prefix']);
            $employee->setFirstName($employeeData['First Name']);
            $employee->setMiddleInitial($employeeData['Middle Initial']);
            $employee->setLastName($employeeData['Last Name']);
            $employee->setGender($employeeData['Gender']);
            $employee->setEmail($employeeData['E Mail']);
            $employee->setDateOfBirth(new \DateTime($employeeData['Date of Birth']));
            $employee->setTimeOfBirth(new \DateTime($employeeData['Time of Birth']));
            $employee->setAgeInYrs($employeeData['Age in Yrs.']);
            $employee->setDateOfJoining(new \DateTime($employeeData['Date of Joining']));
            $employee->setAgeInCompany($employeeData['Age in Company (Years)']);
            $employee->setPhoneNo($employeeData['Phone No. ']);
            $employee->setPlaceName($employeeData['Place Name']);
            $employee->setCounty($employeeData['County']);
            $employee->setCity($employeeData['City']);
            $employee->setZip($employeeData['Zip']);
            $employee->setRegion($employeeData['Region']);

            $em->persist($employee);
        }

        $em->flush();

        $responseArray = array('status' => 'File processed and employees created');
        if ($skippedEmployees) {
            foreach ($skippedEmployees as $skippedEmployee) {
                $responseArray[] = $skippedEmployee;
            }

        }

        return new JsonResponse($responseArray, 201);
    }

    #[Route('/', name: 'get_employees', methods:"GET")]
    public function getAllEmployees(): JsonResponse
    {
        $employees = $this->em->getRepository(Employee::class)->findAll();
        $data = $this->serializer->serialize($employees, 'json');
        return new JsonResponse($data, 200, [], true);
    }

    #[Route('/{employeeID}', name: 'get_employee', methods:"GET")]
    public function getEmployeeById(int $employeeID): JsonResponse
    {
        $employee = $this->em->getRepository(Employee::class)->findOneBy(['employeeID' => $employeeID]);

        if (!$employee) {
            return new JsonResponse(['error' => 'Employee not found'], 404);
        }

        $data = $this->serializer->serialize($employee, 'json');
        return new JsonResponse($data, 200, [], true);
    }

    #[Route('/', name: 'remove_employees', methods:"DELETE")]
    public function removeAllEmployee(): JsonResponse
    {
        $employees = $this->em->getRepository(Employee::class)->findAll();

        foreach ($employees as $employee) {
            $this->em->remove($employee);
        }

        $this->em->flush();

        return new JsonResponse(['status' => 'All employees deleted'], 200);
    }

    #[Route('/{employeeID}', name: 'remove_employee', methods:"DELETE")]
    public function removeEmployeeById(int $employeeID): JsonResponse
    {
        $employee = $this->em->getRepository(Employee::class)->findOneBy(['employeeID' => $employeeID]);

        if (!$employee) {
            return new JsonResponse(['error' => 'Employee not found'], 404);
        }

        $this->em->remove($employee);
        $this->em->flush();

        return new JsonResponse(['status' => 'Employee deleted'], 200);
    }
}
