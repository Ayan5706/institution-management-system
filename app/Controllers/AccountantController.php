<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\StudentFeeModel;
use App\Models\ProgramModel;
use App\Models\SemesterModel;
use App\Models\StudentProfileModel;
use App\Models\UserModel;
use App\Models\EmailChangeRequestModel;
use App\Core\Database;

class AccountantController extends BaseController
{
    private StudentFeeModel $feeModel;
    private ProgramModel $programModel;
    private SemesterModel $semesterModel;
    private StudentProfileModel $studentProfileModel;
    private UserModel $userModel;
    private EmailChangeRequestModel $emailChangeRequests;

    public function __construct()
    {
        $this->feeModel            = new StudentFeeModel();
        $this->programModel        = new ProgramModel();
        $this->semesterModel       = new SemesterModel();
        $this->studentProfileModel = new StudentProfileModel();
        $this->userModel           = new UserModel();
        $this->emailChangeRequests = new EmailChangeRequestModel();
    }

    // ============================================================================
    // DASHBOARD
    // ============================================================================

    public function showDashboard(): void
    {
        $stats = $this->getDashboardStats();

        $this->view('accountant.dashboard', [
            'title'     => 'Accountant Dashboard',
            'user_name' => (string) ($_SESSION['user_name'] ?? 'Accountant'),
            'user_role' => 'ACCOUNTANT',
            'stats'     => $stats,
        ]);
    }

    private function getDashboardStats(): array
    {
        $allFees = $this->feeModel->all() ?? [];

        // Spec: ALL money arithmetic MUST use BCMath — never float
        $totalCollected = '0.00';
        $totalPending   = '0.00';

        foreach ($allFees as $fee) {
            $amountPaid = (string) ($fee['amount_paid'] ?? '0.00');
            $totalCollected = bcadd($totalCollected, $amountPaid, 2);

            $semesterId  = (int) ($fee['semester_id'] ?? 0);
            $semester    = $this->semesterModel->find($semesterId);
            $semesterFee = (string) ($semester['fee_amount'] ?? '0.00');
            $pending     = bcsub($semesterFee, $amountPaid, 2);

            if (bccomp($pending, '0', 2) > 0) {
                $totalPending = bcadd($totalPending, $pending, 2);
            }
        }

        $activeSemesters     = $this->semesterModel->where('is_current', 1) ?? [];
        $activeSemesterCount = count($activeSemesters);

        $students      = $this->userModel->where('role', 'STUDENT') ?? [];
        $totalStudents = count($students);

        $semesters = $this->semesterModel->all('academic_year', 'DESC') ?? [];
        $semesterFees = [];
        foreach ($semesters as $semester) {
            $semesterFees[(int) $semester['id']] = (string) ($semester['fee_amount'] ?? '0.00');
        }

        $paidBySemester = [];
        $dueBySemester = [];
        foreach ($allFees as $fee) {
            $semesterId = (int) ($fee['semester_id'] ?? 0);
            if ($semesterId === 0) {
                continue;
            }

            $amountPaid = (string) ($fee['amount_paid'] ?? '0.00');
            $paidBySemester[$semesterId] = bcadd($paidBySemester[$semesterId] ?? '0.00', $amountPaid, 2);

            $semesterFee = $semesterFees[$semesterId] ?? '0.00';
            $dueBySemester[$semesterId] = bcadd($dueBySemester[$semesterId] ?? '0.00', $semesterFee, 2);
        }

        $collectionSeries = [];
        foreach (array_slice($semesters, 0, 7) as $semester) {
            $semesterId = (int) ($semester['id'] ?? 0);
            $paid = $paidBySemester[$semesterId] ?? '0.00';
            $due = $dueBySemester[$semesterId] ?? '0.00';
            if (bccomp($due, '0', 2) <= 0) {
                $collectionSeries[] = 0;
                continue;
            }

            $ratio = (float) bcdiv($paid, $due, 4);
            $collectionSeries[] = (int) round($ratio * 100);
        }

        while (count($collectionSeries) < 7) {
            $collectionSeries[] = 0;
        }

        return [
            'total_collected'  => $totalCollected,
            'total_pending'    => $totalPending,
            'active_semesters' => $activeSemesterCount,
            'total_students'   => $totalStudents,
            'collection_series' => $collectionSeries,
        ];
    }

    // ============================================================================
    // PROFILE
    // ============================================================================

    public function showProfile(): void
    {
        $userId = (int) ($_SESSION['user_id'] ?? 0);
        if ($userId === 0) {
            $this->redirect('/login');
            return;
        }

        $user = $this->userModel->find($userId);
        if (!$user) {
            http_response_code(404);
            echo 'User not found';
            return;
        }

        $emailRequestPending = $this->emailChangeRequests->hasPendingForUser($userId);

        $this->view('accountant.profile', [
            'title' => 'My Profile',
            'pageSubtitle' => 'Manage your profile details',
            'user' => $user,
            'email_request_pending' => $emailRequestPending,
        ]);
    }

    public function updateProfile(): void
    {
        $userId = (int) ($_SESSION['user_id'] ?? 0);
        if ($userId === 0) {
            $this->json(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        $fullName = trim((string) $this->input('full_name', ''));
        $phone = trim((string) $this->input('phone', ''));

        if ($fullName === '') {
            $this->json(['success' => false, 'message' => 'Full name is required.'], 422);
            return;
        }

        if (strlen($fullName) < 2 || strlen($fullName) > 150) {
            $this->json(['success' => false, 'message' => 'Full name must be between 2 and 150 characters.'], 422);
            return;
        }

        if ($phone !== '' && !preg_match('/^\d+$/', $phone)) {
            $this->json(['success' => false, 'message' => 'Phone number must contain digits only.'], 422);
            return;
        }

        $user = $this->userModel->find($userId);
        if (!$user) {
            $this->json(['success' => false, 'message' => 'User not found.'], 404);
            return;
        }

        $this->userModel->updateById($userId, [
            'full_name' => $fullName,
            'phone' => $phone,
        ]);

        $_SESSION['user_name'] = $fullName;

        $this->json([
            'success' => true,
            'message' => 'Profile updated successfully.',
            'data' => ['full_name' => $fullName, 'phone' => $phone],
        ]);
    }

    public function requestEmailChange(): void
    {
        $userId = (int) ($_SESSION['user_id'] ?? 0);
        if ($userId === 0) {
            $this->json(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        $user = $this->userModel->find($userId);
        if (!$user || strtoupper((string) ($user['role'] ?? '')) !== 'ACCOUNTANT') {
            $this->json(['success' => false, 'message' => 'Forbidden'], 403);
            return;
        }

        if ($this->emailChangeRequests->hasPendingForUser($userId)) {
            $this->json(['success' => false, 'message' => 'You already have a pending email change request.'], 409);
            return;
        }

        $newEmail = trim((string) $this->input('new_email', ''));
        $confirmEmail = trim((string) $this->input('confirm_email', ''));

        if ($newEmail === '' || $confirmEmail === '') {
            $this->json(['success' => false, 'message' => 'New email and confirmation are required.'], 422);
            return;
        }

        if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
            $this->json(['success' => false, 'message' => 'Please enter a valid email address.'], 422);
            return;
        }

        if (strcasecmp($newEmail, $confirmEmail) !== 0) {
            $this->json(['success' => false, 'message' => 'Email confirmation does not match.'], 422);
            return;
        }

        $currentEmail = (string) ($user['email'] ?? '');
        if ($currentEmail !== '' && strcasecmp($currentEmail, $newEmail) === 0) {
            $this->json(['success' => false, 'message' => 'New email must be different from the current email.'], 422);
            return;
        }

        $existing = $this->userModel->firstWhere('email', $newEmail);
        if ($existing) {
            $this->json(['success' => false, 'message' => 'That email address is already in use.'], 409);
            return;
        }

        $this->emailChangeRequests->create([
            'user_id' => $userId,
            'new_email' => $newEmail,
            'status' => 'PENDING',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $this->json([
            'success' => true,
            'message' => 'Email change request submitted for principal approval.',
        ]);
    }

    // ============================================================================
    // FEES MANAGEMENT
    // ============================================================================

    public function showFees(): void
    {
        $this->redirect('/accountant/semester-fees');
    }

    public function showSemesterFees(): void
    {
        $this->view('accountant.semester-fees', [
            'title'               => 'Accountant - Semester Fees',
            'user_name'           => (string) ($_SESSION['user_name'] ?? 'Accountant'),
            'programs'            => $this->getActivePrograms(),
            'selected_program_id' => (int) $this->input('program_id', 0),
            'selected_semester_id'=> (int) $this->input('semester_id', 0),
            'semester_fees'       => $this->getAllSemesterFees(),
        ]);
    }

    public function showStudentFees(): void
    {
        $this->view('accountant.student-fees', [
            'title'               => 'Accountant - Student Fees',
            'user_name'           => (string) ($_SESSION['user_name'] ?? 'Accountant'),
            'programs'            => $this->getActivePrograms(),
            'selected_program_id' => (int) $this->input('program_id', 0),
            'selected_semester_id'=> (int) $this->input('semester_id', 0),
            'selected_status'     => strtoupper(trim((string) $this->input('status', ''))),
        ]);
    }

    // ============================================================================
    // FEE MANAGEMENT API ENDPOINTS
    // ============================================================================

    public function apiGetSemesters(): void
    {
        $semesters = $this->semesterModel->all() ?? [];
        $db        = Database::connection();

        foreach ($semesters as &$semester) {
            $stmt = $db->prepare('SELECT COUNT(*) FROM student_fees WHERE semester_id = :sid');
            $stmt->execute(['sid' => $semester['id']]);
            $semester['student_count'] = (int) $stmt->fetchColumn();
        }

        $this->json(['success' => true, 'data' => $semesters]);
    }

    public function apiGetPrograms(): void
    {
        $this->json(['success' => true, 'data' => $this->getActivePrograms()]);
    }

    public function apiGetProgramSemesters(int $programId): void
    {
        if ($programId <= 0) {
            $this->json(['success' => false, 'message' => 'Program not found.'], 404);
            return;
        }

        $activeOnly = (int) $this->input('active', 0) === 1;
        $semesters  = $this->getProgramSemesters($programId, $activeOnly);
        $this->json(['success' => true, 'data' => $semesters]);
    }

    public function apiGetStudentFees(int $semesterId): void
    {
        $studentFees = $this->buildStudentFeeList($semesterId);
        $this->json(['success' => true, 'data' => $studentFees]);
    }

    public function apiGetFilteredStudentFees(): void
    {
        $programId  = (int) $this->input('program_id', 0);
        $semesterId = (int) $this->input('semester_id', 0);
        $registration = trim((string) $this->input('registration', ''));
        $statusFilter = strtoupper(trim((string) $this->input('status', '')));

        $db = Database::connection();

        $where = ["u.role = 'STUDENT'"];
        $params = [];

        if ($programId > 0) {
            $where[] = 'sp.program_id = :program_id';
            $params['program_id'] = $programId;
        }

        if ($semesterId > 0) {
            $where[] = 'sf.semester_id = :semester_id';
            $params['semester_id'] = $semesterId;
        }

        if ($registration !== '') {
            $where[] = '(sp.registration_number LIKE :reg OR u.login_id LIKE :reg)';
            $params['reg'] = '%' . $registration . '%';
        }

        $sql = "SELECT sf.id AS fee_id, sf.amount_paid, sf.student_id,
                       u.full_name, u.login_id,
                       sp.registration_number,
                       sem.id AS semester_id, sem.semester_number, sem.academic_year, sem.fee_amount
                FROM student_fees sf
                JOIN users u ON u.id = sf.student_id
                JOIN semesters sem ON sem.id = sf.semester_id
                LEFT JOIN student_profiles sp ON sp.user_id = u.id";

        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' ORDER BY u.full_name ASC';

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        $result = [];
        foreach ($rows as $row) {
            $semesterFee = (string) ($row['fee_amount'] ?? '0.00');
            $amountPaid  = (string) ($row['amount_paid'] ?? '0.00');
            $pending     = bcsub($semesterFee, $amountPaid, 2);
            if (bccomp($pending, '0', 2) < 0) {
                $pending = '0.00';
            }

            $status = bccomp($pending, '0', 2) <= 0 ? 'PAID' : 'PENDING';

            if ($statusFilter !== '' && $status !== $statusFilter) {
                continue;
            }

            $result[] = [
                'fee_id'              => (int) $row['fee_id'],
                'student_id'          => (int) $row['student_id'],
                'student_name'        => (string) ($row['full_name'] ?? 'Unknown'),
                'registration_number' => (string) ($row['registration_number'] ?? $row['login_id'] ?? 'N/A'),
                'semester_id'         => (int) $row['semester_id'],
                'semester_label'      => (string) ($row['academic_year'] ?? '') . ' - S' . (int) ($row['semester_number'] ?? 0),
                'fee_amount'          => $semesterFee,
                'amount_paid'         => $amountPaid,
                'pending'             => $pending,
                'status'              => $status,
            ];
        }

        $this->json(['success' => true, 'data' => $result]);
    }


    public function updateSemesterFeeAmount(int $semesterId): void
    {
        $feeAmountRaw = (string) $this->input('fee_amount', '0');

        // Spec: validate with BCMath — must be > 0
        if (!is_numeric($feeAmountRaw) || bccomp($feeAmountRaw, '0', 2) <= 0) {
            $this->json(['success' => false, 'message' => 'Fee amount must be a positive number.'], 422);
            return;
        }

        $feeAmount = number_format((float) $feeAmountRaw, 2, '.', '');

        $semester = $this->semesterModel->find($semesterId);
        if (!$semester) {
            $this->json(['success' => false, 'message' => 'Semester not found.'], 404);
            return;
        }

        $this->semesterModel->updateById($semesterId, ['fee_amount' => $feeAmount]);

        $this->json([
            'success' => true,
            'message' => 'Semester fee amount updated successfully.',
            'data'    => ['semester_id' => $semesterId, 'fee_amount' => $feeAmount],
        ]);
    }

    public function updateStudentPayment(int $feeId): void
    {
        $amountPaidRaw = (string) $this->input('amount_paid', '0');

        // Spec: must use BCMath; cannot be negative
        if (!is_numeric($amountPaidRaw) || bccomp($amountPaidRaw, '0', 2) < 0) {
            $this->json(['success' => false, 'message' => 'Amount paid cannot be negative.'], 422);
            return;
        }

        $fee = $this->feeModel->find($feeId);
        if (!$fee) {
            $this->json(['success' => false, 'message' => 'Fee record not found.'], 404);
            return;
        }

        $semester    = $this->semesterModel->find((int) $fee['semester_id']);
        $semesterFee = (string) ($semester['fee_amount'] ?? '0.00');
        $amountPaid  = number_format((float) $amountPaidRaw, 2, '.', '');

        // Spec: amount_paid must not exceed semester fee_amount
        if (bccomp($amountPaid, $semesterFee, 2) > 0) {
            $this->json([
                'success' => false,
                'message' => 'Amount paid cannot exceed semester fee of ' . $semesterFee,
            ], 422);
            return;
        }

        $this->feeModel->updateById($feeId, ['amount_paid' => $amountPaid]);

        $pending = bcsub($semesterFee, $amountPaid, 2);
        $status  = bccomp($pending, '0', 2) <= 0 ? 'PAID' : 'PENDING';

        $this->json([
            'success' => true,
            'message' => 'Student payment updated successfully.',
            'data'    => [
                'fee_id'      => $feeId,
                'amount_paid' => $amountPaid,
                'pending'     => $pending,
                'status'      => $status,
            ],
        ]);
    }

    // ============================================================================
    // PRIVATE HELPERS
    // ============================================================================

    /** Build enriched student fee records for a given semester using BCMath */
    private function buildStudentFeeList(int $semesterId): array
    {
        $semester    = $this->semesterModel->find($semesterId);
        $semesterFee = (string) ($semester['fee_amount'] ?? '0.00');

        $db   = Database::connection();
        $stmt = $db->prepare('SELECT * FROM student_fees WHERE semester_id = :sid ORDER BY student_id ASC');
        $stmt->execute(['sid' => $semesterId]);
        $allFees = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        $result = [];
        foreach ($allFees as $fee) {
            $student        = $this->userModel->find((int) $fee['student_id']);
            $studentProfile = $this->studentProfileModel->findByUserId((int) $fee['student_id']);
            $amountPaid     = (string) ($fee['amount_paid'] ?? '0.00');
            $pending        = bcsub($semesterFee, $amountPaid, 2);
            // BCMath clamp: max(0, pending)
            if (bccomp($pending, '0', 2) < 0) {
                $pending = '0.00';
            }
            $status = bccomp($pending, '0', 2) <= 0 ? 'PAID' : 'PENDING';

            $result[] = [
                'id'                  => $fee['id'],
                'student_id'          => $fee['student_id'],
                'student_name'        => $student['full_name'] ?? 'Unknown',
                'registration_number' => $studentProfile['registration_number'] ?? ($student['login_id'] ?? 'N/A'),
                'fee_amount'          => $semesterFee,
                'amount_paid'         => $amountPaid,
                'pending'             => $pending,
                'status'              => $status,
            ];
        }

        return $result;
    }

    /** @return array<int, array<string, mixed>> */
    private function getActivePrograms(): array
    {
        $db = Database::connection();
        $stmt = $db->query('SELECT * FROM programs WHERE is_active = 1 ORDER BY program_name ASC');
        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    /** @return array<int, array<string, mixed>> */
    private function getProgramSemesters(int $programId, bool $activeOnly): array
    {
        $db = Database::connection();
        $sql = 'SELECT id, program_id, semester_number, academic_year, is_current, fee_amount
                FROM semesters
                WHERE program_id = :program_id';

        if ($activeOnly) {
            $sql .= ' AND is_current = 1';
        }

        $sql .= ' ORDER BY academic_year DESC, semester_number DESC';

        $stmt = $db->prepare($sql);
        $stmt->execute(['program_id' => $programId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    /** @return array<int, array<string, mixed>> */
    private function getAllSemesterFees(): array
    {
        $db = Database::connection();
        $sql = 'SELECT s.id, s.program_id, s.semester_number, s.academic_year, s.is_current, s.fee_amount,
                       p.program_name
                FROM semesters s
                JOIN programs p ON p.id = s.program_id
                ORDER BY p.program_name ASC, s.academic_year DESC, s.semester_number DESC';

        $stmt = $db->query($sql);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

}
