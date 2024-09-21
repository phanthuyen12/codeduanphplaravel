<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\LevelDBController;

class HospitalController extends Controller
{
    protected $levelDB;

    public function __construct(LevelDBController $levelDB)
    {
        $this->levelDB = $levelDB;
    }

  
    public function createHospital(Request $request)
    {
        // Lưu thông tin bệnh viện vào LevelDB
        $hospitalData = [
            'tokenorg' => $request->input('tokenorg'),
            'name' => $request->input('nameorg'),
            'admin' => $request->input('nameadmin'),
            'email' => $request->input('emailadmin'),
            'address' => $request->input('addressadmin'),
            'cccd' => $request->input('cccdadmin'),
            'phone' => $request->input('phoneadmin'),
            'password' => bcrypt($request->input('passworkadmin')),
            'business' => $request->input('businessBase64'), // Thêm dấu phẩy ở đây
          
        ];
    
        // Gọi hàm putData từ LevelDBController để lưu thông tin bệnh viện
        return $this->levelDB->putData('hospital_' . $hospitalData['tokenorg'], json_encode($hospitalData));
    }
    
   
public function getHospital(Request $request)
{
    // Lấy tên bệnh viện từ request
    $hospitalName = $request->input('hospitalName');
    
    // Kiểm tra xem tên bệnh viện có tồn tại không
    if (!$hospitalName) {
        return response()->json(['error' => 'Hospital name is required'], 400);
    }

    // Gọi hàm getData từ LevelDBController để lấy thông tin bệnh viện
    return $this->levelDB->getData('hospital_' . $hospitalName);

}


}
