<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\LevelDBController;

class MedicalController extends Controller
{
    //
    protected $levelDB;

    public function __construct(LevelDBController $levelDB)
    {
        $this->levelDB = $levelDB;
    }
   

    public function createMedicalRecord(Request $request)
    {
        // Xác thực dữ liệu đầu vào
        $medicalData = [
            'fullName' => $request->input('fullName'),
            'numbercccd' => $request->input('numbercccd'),
            'dateOfBirth' => $request->input('informationpatient.dateOfBirth'), // Đúng
            'gender' => $request->input('informationpatient.gender'), // Đúng
            'phone' => $request->input('informationpatient.phone'), // Đúng
            'address' => $request->input('informationpatient.address'), // Đúng
            'idCard' => $request->input('informationpatient.idCard'), // Đúng
            'hospitalAccessRequirements' => [],
            'medicalHistory' => [
                'hospital'=>[]
            ],
        ];
    
            $existingRecord = $this->levelDB->getData('medical_'.$medicalData['numbercccd']);

            if (!$existingRecord==false) {
                return response()->json(['message' => 'Số CCCD đã tồn tại trong hệ thống.', 'data' => $existingRecord], 409);
            }
       
        
    
        // Lưu dữ liệu vào LevelDB
        $result = $this->levelDB->putData('medical_'.$medicalData['numbercccd'], json_encode($medicalData));

    
        // Trả về phản hồi
        if ($result) {
            return response()->json(['message' => 'Sổ khám bệnh được tạo thành công.', 'datas' => $result], 201);
        } else {
            return response()->json(['message' => 'Tạo sổ khám bệnh thất bại.'], 500);
        }
    }
    public function getDataMedicalRecord(Request $request){
        $cccdnumber = $request->input('cccdnumber');
        try {
            $existingRecord = $this->levelDB->getData('medical_'.$cccdnumber);

            if ($existingRecord) {
                return response()->json(['message' => 'Số CCCD đã tồn tại trong hệ thống.', 'data' => $existingRecord], 201);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Đã xảy ra lỗi khi truy vấn LevelDB.', 'error' => $e->getMessage()], 500);
        }
        
    }


    public function addHospitalAccessRequirement(Request $request)
    {
        // Lấy số CCCD từ request
        $cccdnumber = $request->input('cccdnumber');
    
        // Lấy dữ liệu hiện tại từ LevelDB
        $existingRecord = $this->levelDB->getData('medical_' . $cccdnumber);
    
        // Kiểm tra nếu dữ liệu bệnh nhân tồn tại
        if (!isset($existingRecord['value']['hospitalAccessRequirements'])) {
            $existingRecord['value']['hospitalAccessRequirements'] = []; // Khởi tạo mảng rỗng nếu không tồn tại
        }
    
        // Chuyển đổi dữ liệu JSON thành mảng
        // $medicalData = json_decode($existingRecord, true); // Giả sử đây là chuỗi JSON
    
        // Dữ liệu mới cần thêm vào hospitalAccessRequirements
        $newRequirement = [
            'hospitalName' => $request->input('hospitalName'),
            'time' => $request->input('time'),
            'status' => $request->input('status') === 'true', // Chuyển đổi chuỗi "true" thành boolean
        ];
    
        // Thêm yêu cầu mới vào hospitalAccessRequirements
        $existingRecord['value']['hospitalAccessRequirements'][] = $newRequirement;

    
        // Lưu lại dữ liệu đã cập nhật vào LevelDB
        return $this->levelDB->putData('medical_' . $cccdnumber, json_encode($existingRecord));
    }
    public function addMedicalHistoriesHospital(Request $request)
    {
        // Lấy số CCCD từ request
        $cccdnumber = $request->input('cccdnumber');
    
        // Lấy dữ liệu hiện tại từ LevelDB
        $existingRecord = $this->levelDB->getData('medical_' . $cccdnumber);
    
        // Kiểm tra nếu dữ liệu bệnh nhân tồn tại
        if (!$existingRecord) {
            return response()->json(['error' => 'Bệnh nhân không tồn tại'], 404);
        }
    
        // Chuyển đổi dữ liệu JSON thành mảng nếu cần
        // $existingRecord = json_decode($existingRecord, true);
    
        // Kiểm tra nếu trường 'medicalHistory' không tồn tại
        if (!isset($existingRecord['value']['medicalHistory'])) {
            return response()->json(['error' => 'medicalHistory không tồn tại, không thể chèn thêm dữ liệu'], 400);
        }
    
        // Tạo bản ghi mới cho bệnh viện
        $newMedicalRecord = [
            'name' => $request->input('name'),
            'address' => $request->input('address'),
            'departments' => $request->input('departments'),
            'appointment'=>[]
        ];
    
        // Thêm bản ghi mới vào medicalHistory
        $existingRecord['value']['medicalHistory']['hospital']= $newMedicalRecord;
    
        // Lưu lại dữ liệu đã cập nhật vào LevelDB (chỉ lưu phần 'value' thôi, không lặp lại key)
        $this->levelDB->putData('medical_' . $cccdnumber, json_encode($existingRecord['value']));
    
        // Trả về phản hồi thành công
        return response()->json(['message' => 'Cập nhật thành công'], 200);
    }
    
    public function addAppointmentHospital(Request $request)
    {
        // Lấy số CCCD từ request
        $cccdnumber = $request->input('cccdnumber');
    
        // Lấy dữ liệu hiện tại từ LevelDB
        $existingRecord = $this->levelDB->getData('medical_' . $cccdnumber);
    
        // Kiểm tra nếu dữ liệu bệnh nhân tồn tại
        if (!$existingRecord) {
            return response()->json(['error' => 'Bệnh nhân không tồn tại'], 404);
        }
    
        // Kiểm tra nếu trường 'medicalHistory' không tồn tại
        if (!isset($existingRecord['value']['medicalHistory'])) {
            return response()->json(['error' => 'medicalHistory không tồn tại, không thể chèn thêm dữ liệu','data'=>$existingRecord], 400);
        }
    
        // Tạo bản ghi mới cho cuộc hẹn
        $newAppointment = [
            'date' => $request->input('date'),              // Ngày hẹn
            'time' => $request->input('time'),              // Thời gian
            'doctor' => $request->input('doctor'),          // Bác sĩ phụ trách
            'reason' => $request->input('reason'),          // Lý do khám
            'results' => $request->input('results', 'Chưa có kết quả'), // Kết quả
            'examinations'=>[],
            'conditions'=>[],
            
        ];
    
        // Kiểm tra xem 'appointments' có tồn tại trong medicalHistory hay chưa
        if (!isset($existingRecord['value']['medicalHistory']['appointments'])) {
            $existingRecord['value']['medicalHistory']['appointments'] = [];
        }
    
        // Thêm cuộc hẹn mới vào danh sách 'appointments'
        $existingRecord['value']['medicalHistory']['appointments'][] = $newAppointment;
    
        // Lưu lại dữ liệu đã cập nhật vào LevelDB
        $this->levelDB->putData('medical_' . $cccdnumber, json_encode($existingRecord['value']));
    
        // Trả về phản hồi thành công
        return response()->json(['message' => 'Thêm cuộc hẹn thành công'], 200);
    }
    
    public function addExaminationHospital(Request $request)
{
    // Lấy số CCCD từ request
    $cccdnumber = $request->input('cccdnumber');

    // Lấy dữ liệu hiện tại từ LevelDB
    $existingRecord = $this->levelDB->getData('medical_' . $cccdnumber);

    // Kiểm tra nếu dữ liệu bệnh nhân tồn tại
    if (!$existingRecord) {
        return response()->json(['error' => 'Bệnh nhân không tồn tại'], 404);
    }

    // Kiểm tra nếu trường 'medicalHistory' không tồn tại
    if (!isset($existingRecord['value']['medicalHistory'])) {
        return response()->json(['error' => 'medicalHistory không tồn tại, không thể chèn thêm dữ liệu', 'data' => $existingRecord], 400);
    }

    // Kiểm tra xem 'appointments' có tồn tại hay không
    if (empty($existingRecord['value']['medicalHistory']['appointments'])) {
        return response()->json(['error' => 'Chưa có cuộc hẹn nào để thêm xét nghiệm'], 400);
    }

    // Lấy cuộc hẹn gần nhất (hoặc bạn có thể chọn cuộc hẹn cụ thể theo logic của bạn)
    $latestAppointment = &$existingRecord['value']['medicalHistory']['appointments'][count($existingRecord['value']['medicalHistory']['appointments']) - 1];

    // Tạo bản ghi mới cho xét nghiệm
    $newExamination = [
        'test' => $request->input('test'),              // Loại xét nghiệm
        'date' => $request->input('date'),              // Ngày xét nghiệm
        'results' => $request->input('results', 'Chưa có kết quả') // Kết quả
    ];

    // Kiểm tra xem 'examinations' có tồn tại trong cuộc hẹn hay không
    if (!isset($latestAppointment['examinations'])) {
        $latestAppointment['examinations'] = [];
    }

    // Thêm xét nghiệm mới vào danh sách 'examinations'
    $latestAppointment['examinations'][] = $newExamination;

    // Lưu lại dữ liệu đã cập nhật vào LevelDB
    $this->levelDB->putData('medical_' . $cccdnumber, json_encode($existingRecord['value']));

    // Trả về phản hồi thành công
    return response()->json(['message' => 'Thêm xét nghiệm thành công'], 200);
}
public function addConditionHospital(Request $request)
{
    // Lấy số CCCD từ request
    $cccdnumber = $request->input('cccdnumber');

    // Lấy dữ liệu hiện tại từ LevelDB
    $existingRecord = $this->levelDB->getData('medical_' . $cccdnumber);

    // Kiểm tra nếu dữ liệu bệnh nhân tồn tại
    if (!$existingRecord) {
        return response()->json(['error' => 'Bệnh nhân không tồn tại'], 404);
    }

    // Kiểm tra nếu trường 'medicalHistory' không tồn tại
    if (!isset($existingRecord['value']['medicalHistory'])) {
        return response()->json(['error' => 'medicalHistory không tồn tại, không thể chèn thêm dữ liệu', 'data' => $existingRecord], 400);
    }

    // Kiểm tra xem 'appointments' có tồn tại hay không
    if (empty($existingRecord['value']['medicalHistory']['appointments'])) {
        return response()->json(['error' => 'Chưa có cuộc hẹn nào để thêm tình trạng bệnh'], 400);
    }

    // Lấy cuộc hẹn gần nhất (hoặc bạn có thể chọn cuộc hẹn cụ thể theo logic của bạn)
    $latestAppointment = &$existingRecord['value']['medicalHistory']['appointments'][count($existingRecord['value']['medicalHistory']['appointments']) - 1];

    // Tạo bản ghi mới cho tình trạng bệnh
    $newCondition = [
        'condition' => $request->input('condition'),              // Tình trạng bệnh
        'dateDiagnosed' => $request->input('dateDiagnosed'),      // Ngày chẩn đoán
        'treatment' => $request->input('treatment'),              // Phương pháp điều trị
        'prescription' => $request->input('prescription', [])      // Đơn thuốc (mảng)
    ];

    // Kiểm tra xem 'conditions' có tồn tại trong cuộc hẹn hay không
    if (!isset($latestAppointment['conditions'])) {
        $latestAppointment['conditions'] = [];
    }

    // Thêm tình trạng bệnh mới vào danh sách 'conditions'
    $latestAppointment['conditions'][] = $newCondition;

    // Lưu lại dữ liệu đã cập nhật vào LevelDB
    $this->levelDB->putData('medical_' . $cccdnumber, json_encode($existingRecord['value']));

    // Trả về phản hồi thành công
    return response()->json(['message' => 'Thêm tình trạng bệnh thành công'], 200);
}

        
    
}
