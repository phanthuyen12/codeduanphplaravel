<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LevelDBController extends Controller
{
    protected $db;

    // Hàm khởi tạo để kết nối với LevelDB
    public function __construct()
    {
        // Đường dẫn đến cơ sở dữ liệu LevelDB
        $databasePath = storage_path('organization');
        
        // Nếu thư mục không tồn tại, tạo mới thư mục
        if (!file_exists($databasePath)) {
            mkdir($databasePath, 0777, true);
        }

        // Kết nối tới LevelDB
        $this->db = new \LevelDB($databasePath);
    }

    // Hàm thêm dữ liệu vào LevelDB
    public function putData($key, $value)
    {
        try {
            $this->db->put($key, $value);
            // Sử dụng response()->json() nhưng không có các phần meta khác
            return response()->json(['message' => "Dữ liệu đã được lưu với key: $key"], 200, [], JSON_FORCE_OBJECT);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Đã xảy ra lỗi khi lưu dữ liệu.'], 500, [], JSON_FORCE_OBJECT);
        }
    }
    

    // Hàm lấy dữ liệu từ LevelDB
    public function getData($key)
    {
        $value = $this->db->get($key);
    
        if ($value === false) {
            return false;
        }
    
        return [
            'key' => $key,
            'value' => json_decode($value, true)
        ];
    }
    
    // Hàm xóa dữ liệu từ LevelDB
    public function deleteData($key)
    {
        $this->db->delete($key);
        return response()->json(['message' => "Dữ liệu với key: $key đã bị xóa."]);
    }

    // Hàm đóng kết nối LevelDB (có thể không cần gọi thủ công, vì PHP sẽ tự động đóng khi script kết thúc)
    public function __destruct()
    {
        $this->db->close();
    }
}
