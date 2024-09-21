<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\LevelDBController;

class MemberController extends Controller

{
    protected $levelDB;

    public function __construct(LevelDBController $levelDB)
    {
        $this->levelDB = $levelDB;
    }
    public function getMember(Request $request)
{
    // Lấy tên bệnh viện từ request
    $hospitalName = $request->input('hospitalName');
    
    // Kiểm tra xem tên bệnh viện có tồn tại không
    if (!$hospitalName) {
        return response()->json(['error' => 'Hospital name is required'], 400);
    }

    // Gọi hàm getData từ LevelDBController để lấy thông tin bệnh viện
    return $this->levelDB->getData('member_' . $hospitalName);
}
    
    public function createMember(Request $request)
    {   
        $tokenorg = $request->input('tokenorg');
    
        // Kiểm tra thông tin đầu vào
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'phone' => 'required|string|max:15',
                'role' => 'required|string', // Ví dụ: "doctor", "nurse", v.v.
                'password' => 'required|string|min:6',
            ]);
            
            // Tạo thông tin thành viên
            $memberData = [
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'phone' => $validatedData['phone'],
                'role' => $validatedData['role'],
                'password' => bcrypt($validatedData['password']),
            ];
            
            // Gọi hàm putData từ LevelDBController để lưu thông tin thành viên
            $tokenMember = 'member_' . $tokenorg . '_' . $memberData['email'];
            $result =  $this->levelDB->putData('member_' .$tokenMember, json_encode($memberData));

              // Trả về dữ liệu thành viên vừa tạo
            return response()->json(['success' => true, 'data' => $result], 201);

            
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Xử lý lỗi xác thực
            return response()->json(['error' => $e->validator->errors()], 422);
        } catch (\Exception $e) {
            // Xử lý lỗi khác
            return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }
    
    
}

    
    

