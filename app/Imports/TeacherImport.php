<?php

namespace App\Imports;

use App\Mail\UserRegisteredMail;
use App\Models\Teacher;
use App\Models\Statistic;
use App\Traits\ApiResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Symfony\Component\HttpFoundation\Response;

class TeacherImport implements WithHeadingRow, ToCollection, WithValidation
{
    protected $teacherRepository;

    protected $userRepository;

    public function __construct($teacherRepository, $userRepository)
    {
        $this->teacherRepository = $teacherRepository;
        $this->userRepository = $userRepository;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            DB::beginTransaction();
            try {
                $data = [
                    'name' => $row['name'],
                    'email' => $row['email'],
                    'department_id' => $row['department_id']
                ];

                $data['user_name'] = 'teacher';
                if (empty($data['password'])) {
                    $password = $this->generateRandomPassword();
                    $data['password'] = $password;
                } else {
                    $password = $data['password']; // Lưu lại mật khẩu gốc để gửi email
                }

                // Mã hóa mật khẩu
                $data['password'] = Hash::make($data['password']);
                $data['active'] = 1;
                $data['role_id'] = 3;

                $user = $this->userRepository->create($data);
                $teacher = $this->teacherRepository->create([
                    'user_id' => $user->id,
                    'department_id' => $data['department_id']
                ]);
                Statistic::where('name', 'total_teachers')->increment('value');
                $teacher->teacher_code = 'TEACHER' . str_pad($teacher->teacher_id, 3, '0', STR_PAD_LEFT);
                $teacher->save();
                $user->user_name = $teacher->teacher_code;
                $user->save();

                Mail::to($user->email)->send(new UserRegisteredMail($user, $password));
                DB::commit();
            } catch (\Exception $e) {
               throw new \Exception('Error importing teacher: ' . $e->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required'],
            'email' => ['required'],
            'department_id' => ['required']
        ];
    }

    public function customValidationMessages()
    {
        return [
            'name.required' => 'Thiếu tên giảng viên.',
            'email.required' => 'Thiếu email.',
            'department_id.required' => 'Thiếu mã khoa.',
        ];
    }

    private function generateRandomPassword($length = 12)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $password = '';
        $maxIndex = strlen($chars) - 1;

        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[random_int(0, $maxIndex)];
        }

        return $password;
    }
}
