<?php

namespace App\Imports;

use App\Mail\UserRegisteredMail;
use App\Models\Student;
use App\Models\Statistic;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Symfony\Component\HttpFoundation\Response;

class StudentImport implements WithHeadingRow, ToCollection, WithValidation
{
    protected $studentRepository;

    protected $userRepository;

    public function __construct($studentRepository, $userRepository)
    {
        $this->studentRepository = $studentRepository;
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
                    'admission_year' => $row['admission_year'],
                    'class_id' => $row['class_id']
                ];

                $data['user_name'] = 'student';
                if (empty($data['password'])) {
                    $password = $this->generateRandomPassword();
                    $data['password'] = $password;
                } else {
                    $password = $data['password']; // Lưu lại mật khẩu gốc để gửi email
                }

                // Mã hóa mật khẩu
                $data['password'] = Hash::make($data['password']);
                $data['active'] = 1;
                $data['role_id'] = 2;

                $user = $this->userRepository->create($data);
                $student = $this->studentRepository->create([
                    'user_id' => $user->id,
                    'class_id' => $data['class_id'],
                    'admission_year' => $data['admission_year'],
                ]);
                Statistic::where('name', 'total_students')->increment('value');
                $student->student_code = 'STUDENT' . str_pad($student->student_id, 3, '0', STR_PAD_LEFT);
                $student->save();
                $user->user_name = $student->student_code;
                $user->save();
                // Gửi email cho người dùng
                Mail::to($user->email)->send(new UserRegisteredMail($user, $password));

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                // Có thể log lỗi nếu cần
                continue;
            }
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required'],
            'email' => ['required'],
            'admission_year' => ['required'],
            'class_id' => ['required']
        ];
    }

    public function customValidationMessages()
    {
        return [
            'name.required' => 'Thiếu tên sinh viên.',
            'email.required' => 'Thiếu email.',
            'admission_year.required' => 'Thiếu năm nhập học.',
            'class_id.required' => 'Thiếu mã lớp.',
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
