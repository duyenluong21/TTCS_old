<?php
require_once 'database.php';


function error422($message){

    $data = [
        'status' => 422,
        'message' => $message,
    ];
    header("HTTP/1.0 422 Unprocessable Entity");
    echo json_encode($data);
    exit();
}

//-----------------------------------------------------------Airline-------------------------------------------------------
function storeAirline($airlineInput){
    global $conn;

    $tenMayBay = mysqli_real_escape_string($conn, $airlineInput['tenMayBay']);
    $hangMayBay = mysqli_real_escape_string($conn, $airlineInput['hangMayBay']);
    $gheToiDa = mysqli_real_escape_string($conn, $airlineInput['gheToiDa']);
      // Kiểm tra trùng lặp
      $duplicateQuery = "SELECT COUNT(*) AS count FROM maybay 
      WHERE tenMayBay = '$tenMayBay' 
      AND hangMayBay = '$hangMayBay' 
      AND gheToiDa = '$gheToiDa'";
    $duplicateResult = mysqli_query($conn, $duplicateQuery);
    if ($duplicateResult) {
        $duplicateRow = mysqli_fetch_assoc($duplicateResult);
        $duplicateCount = $duplicateRow['count'];

        if ($duplicateCount > 0) {
            $data = [
                'status' => 400,
                'message' => 'Đã có máy bay này. Vui lòng kiểm tra lại.',
            ];
            header("HTTP/1.0 400 Bad Request");
            echo json_encode($data);
            return;
        }
    } else {
        $data = [
            'status' => 500,
            'message' => 'Internal server error during duplicate check.',
        ];
        header("HTTP/1.0 500 Internal Server Error");
        echo json_encode($data);
        return;
    }
    if(empty(trim($tenMayBay))){
        return error422('Hãy nhập tên máy bay');
    }
    elseif(empty(trim($hangMayBay))){
        return error422('Hãy nhập hãng may bay');
    }
    elseif(empty(trim($gheToiDa))){
        return error422('Hãy nhập ghế tối đa');
    }
    else{
        $query = "INSERT INTO maybay (tenMayBay,hangMayBay,gheToiDa) VALUES ('$tenMayBay','$hangMayBay','$gheToiDa')";
        $result = mysqli_query($conn,$query);

        if($result){

            $data = [
                'status' => 201,
                'messange' => 'Máy bay đã được thêm thành công',
            ];
            header("HTTP/1.0 201 Created");
            echo json_encode($data);

        }else{
            $data = [
                'status' => 500,
                'messange' => 'Internal server error',
            ];
            header("HTTP/1.0 500 Method not allowed");
            echo json_encode($data);
        }
    }

}
function getAirlineList(){

    global $conn;
    $query = "SELECT * FROM maybay";
    $query_run = mysqli_query($conn,$query);

    if($query_run){

        if(mysqli_num_rows($query_run) > 0){

            $res = mysqli_fetch_all($query_run, MYSQLI_ASSOC);

            $data = [
                'status' => 200,
                'message' => 'Customer List Fetched Successfully',
                'data' => $res
            ];
            header("HTTP/1.0 200 OK");
            return json_encode($data);
        }else{
            $data = [
                'status' => 405,
                'messange' =>  'No airline found',
            ];
            header("HTTP/1.0 405 Method not allowed");
            echo json_encode($data);
        }
    }else{
        $data = [
            'status' => 500,
            'messange' => 'Internal server error',
        ];
        header("HTTP/1.0 500 Internal server error");
        echo json_encode($data);
    }
}

function getAirline($airlineParams){
    global $conn;
    if($airlineParams['maMB'] == null){
        return error422('Nhập mã máy bay');
    }

    $airlineId = mysqli_real_escape_string($conn,$airlineParams['maMB']);
    $query = "SELECT * FROM maybay WHERE maMB = '$airlineId' LIMIT 1";
    $result = mysqli_query($conn,$query);

    if($result){

        if(mysqli_num_rows($result) == 1){
            $res = mysqli_fetch_assoc($result);
            $data = [
                'status' => 200,
                'message' => 'Airline Fetched Successfully',
                'data' => $res
            ];
            header("HTTP/1.0 200 OK");
            return json_encode($data);
        }else{
            $data = [
                'status' => 404,
                'message' => 'Không có máy bay nào được tìm thấy'
            ];
            header("HTTP/1.0 404 Internal server error");
            echo json_encode($data);
        }
    }else{
        $data = [
            'status' => 500,
            'messange' => 'Internal server error',
        ];
        header("HTTP/1.0 500 Internal server error");
        echo json_encode($data);
    }
}

function updateAirline($airlineInput, $airlineParams){
    global $conn;

    if(!isset($airlineParams['maMB'])){
        return error422('Mã máy bay không tìm thấy');
    }elseif($airlineParams['maMB'] == null){
        return error422('Nhập mã máy bay');
    }

    $airlineId = intval(mysqli_real_escape_string($conn, $airlineParams['maMB']));
    $tenMayBay = mysqli_real_escape_string($conn, $airlineInput['tenMayBay']);
    $hangMayBay = mysqli_real_escape_string($conn, $airlineInput['hangMayBay']);
    $gheToiDa = mysqli_real_escape_string($conn, $airlineInput['gheToiDa']);

    if(empty(trim($tenMayBay))){
        return error422('Hãy nhập tên máy bay');
    }
    elseif(empty(trim($hangMayBay))){
        return error422('Hãy nhập hãng may bay');
    }
    elseif(empty(trim($gheToiDa))){
        return error422('Hãy nhập ghế tối đa');
    }
    else{
        $query = "UPDATE maybay SET tenMayBay='$tenMayBay',hangMayBay = '$hangMayBay',gheToiDa = '$gheToiDa' WHERE maMB = '$airlineId' LIMIT 1";
        $result = mysqli_query($conn,$query);

        if($result){

            $data = [
                'status' => 200,
                'messange' => 'Máy bay đã được sửa thành công',
            ];
            header("HTTP/1.0 200 Success");
            echo json_encode($data);

        }else{
            $data = [
                'status' => 500,
                'messange' => 'Internal server error',
            ];
            header("HTTP/1.0 500 Method not allowed");
            echo json_encode($data);
        }
    }

}

function deleteAirline($airlineParams){
    global $conn;

    if(!isset($airlineParams['maMB'])){
        return error422('Mã máy bay không tìm thấy');
    }elseif($airlineParams['maMB'] == null){
        return error422('Nhập mã máy bay');
    }

    $airlineId = mysqli_real_escape_string($conn,$airlineParams['maMB']);

    $query = "DELETE FROM maybay WHERE maMB = '$airlineId' LIMIT 1";
    $result = mysqli_query($conn,$query);

    if($result){
        $data = [
            'status' => 204,
            'messange' => 'Xóa thành công',
        ];
        header("HTTP/1.0 204 Deleted");
        echo json_encode($data);
    }else{
        $data = [
            'status' => 404,
            'messange' => 'Không tìm thấy máy bay',
        ];
        header("HTTP/1.0 404 Not Found");
        echo json_encode($data);
    }
}   
//end airline

//----------------------------------Aiport---------------------------------------------------------------
function getAirportList(){

    global $conn;
    $query = "SELECT * FROM sanbay";
    $query_run = mysqli_query($conn,$query);

    if($query_run){

        if(mysqli_num_rows($query_run) > 0){

            $res = mysqli_fetch_all($query_run, MYSQLI_ASSOC);

            $data = [
                'status' => 200,
                'message' => 'Airport List Fetched Successfully',
                'data' => $res
            ];
            header("HTTP/1.0 200 OK");
            return json_encode($data);
        }else{
            $data = [
                'status' => 405,
                'messange' =>  'No airline found',
            ];
            header("HTTP/1.0 405 Method not allowed");
            echo json_encode($data);
        }
    }else{
        $data = [
            'status' => 500,
            'messange' => 'Internal server error',
        ];
        header("HTTP/1.0 500 Internal server error");
        echo json_encode($data);
    }
}

function getAirport($airportParams){
    global $conn;
    if($airportParams['maSanBay'] == null){
        return error422('Nhập mã máy bay');
    }

    $airportId = mysqli_real_escape_string($conn,$airportParams['maSanBay']);
    $query = "SELECT * FROM sanbay WHERE maSanBay = '$airportId' LIMIT 1";
    $result = mysqli_query($conn,$query);

    if($result){

        if(mysqli_num_rows($result) == 1){
            $res = mysqli_fetch_assoc($result);
            $data = [
                'status' => 200,
                'message' => 'Airport Fetched Successfully',
                'data' => $res
            ];
            header("HTTP/1.0 200 OK");
            return json_encode($data);
        }else{
            $data = [
                'status' => 404,
                'message' => 'Không có sân bay nào được tìm thấy'
            ];
            header("HTTP/1.0 404 Internal server error");
            echo json_encode($data);
        }
    }else{
        $data = [
            'status' => 500,
            'messange' => 'Internal server error',
        ];
        header("HTTP/1.0 500 Internal server error");
        echo json_encode($data);
    }
}

function storeAirport($airportInput){
    global $conn;

    $tenSanBay = mysqli_real_escape_string($conn, $airportInput['tenSanBay']);
    $diaDiem = mysqli_real_escape_string($conn, $airportInput['diaDiem']);
    // Kiểm tra trùng lặp
    $duplicateQuery = "SELECT COUNT(*) AS count FROM sanbay 
                   WHERE tenSanBay = '$tenSanBay' 
                   OR diaDiem = '$diaDiem' ";
    $duplicateResult = mysqli_query($conn, $duplicateQuery);
    if ($duplicateResult) {
        $duplicateRow = mysqli_fetch_assoc($duplicateResult);
        $duplicateCount = $duplicateRow['count'];

        if ($duplicateCount > 0) {
            $data = [
                'status' => 400,
                'message' => 'Đã có chuyến bay này. Vui lòng kiểm tra lại.',
            ];
            header("HTTP/1.0 400 Bad Request");
            echo json_encode($data);
            return;
        }
    } else {
        $data = [
            'status' => 500,
            'message' => 'Internal server error during duplicate check.',
        ];
        header("HTTP/1.0 500 Internal Server Error");
        echo json_encode($data);
        return;
    }
    if(empty(trim($tenSanBay))){
        return error422('Hãy nhập tên sân bay');
    }
    elseif(empty(trim($diaDiem))){
        return error422('Hãy nhập địa điểm');
    }
    else{
        $query = "INSERT INTO sanbay (tenSanBay, diaDiem) VALUES ('$tenSanBay','$diaDiem')";
        $result = mysqli_query($conn,$query);

        if($result){

            $data = [
                'status' => 201,
                'messange' => 'Sân bay đã được thêm thành công',
            ];
            header("HTTP/1.0 201 Created");
            echo json_encode($data);

        }else{
            $data = [
                'status' => 500,
                'messange' => 'Internal server error',
            ];
            header("HTTP/1.0 500 Method not allowed");
            echo json_encode($data);
        }
    }

}

function deleteAirport($airportParams){
    global $conn;

    if(!isset($airportParams['maSanBay'])){
        return error422('Mã sân bay không tìm thấy');
    }elseif($airportParams['maSanBay'] == null){
        return error422('Nhập mã sân bay');
    }

    $airportId = mysqli_real_escape_string($conn,$airportParams['maSanBay']);

    $query = "DELETE FROM sanbay WHERE maSanBay = '$airportId' LIMIT 1";
    $result = mysqli_query($conn,$query);

    if($result){
        $data = [
            'status' => 204,
            'messange' => 'Xóa thành công',
        ];
        header("HTTP/1.0 204 Deleted");
        echo json_encode($data);
    }else{
        $data = [
            'status' => 404,
            'messange' => 'Không tìm thấy sân bay',
        ];
        header("HTTP/1.0 404 Not Found");
        echo json_encode($data);
    }
}  

function updateAirport($airportInput, $airportParams){
    global $conn;

    if(!isset($airportParams['maSanBay'])){
        return error422('Mã sân bay không tìm thấy');
    }elseif($airportParams['maSanBay'] == null){
        return error422('Nhập mã sân bay');
    }

    $airportId = mysqli_real_escape_string($conn,$airportParams['maSanBay']);
    $tenSanBay = mysqli_real_escape_string($conn, $airportInput['tenSanBay']);
    $diaDiem = mysqli_real_escape_string($conn, $airportInput['diaDiem']);

    if(empty(trim($tenSanBay))){
        return error422('Hãy nhập tên sân bay');
    }
    elseif(empty(trim($diaDiem))){
        return error422('Hãy nhập địa điểm');
    }
    else{
        $query = "UPDATE sanbay SET tenSanBay='$tenSanBay',diaDiem = '$diaDiem' WHERE maSanBay = '$airportId' LIMIT 1";
        $result = mysqli_query($conn,$query);

        if($result){

            $data = [
                'status' => 200,
                'messange' => 'Sân bay đã được sửa thành công',
            ];
            header("HTTP/1.0 200 Success");
            echo json_encode($data);

        }else{
            $data = [
                'status' => 500,
                'messange' => 'Internal server error',
            ];
            header("HTTP/1.0 500 Method not allowed");
            echo json_encode($data);
        }
    }

}

//end airport

//--------------------------------------------------------------------Passenger----------------------------------
function getPassengerList(){

    global $conn;
    $query = "SELECT * FROM khachhang";
    $query_run = mysqli_query($conn,$query);

    if($query_run){

        if(mysqli_num_rows($query_run) > 0){

            $res = mysqli_fetch_all($query_run, MYSQLI_ASSOC);

            $data = [
                'status' => 200,
                'message' => 'Customer List Fetched Successfully',
                'data' => $res
            ];
            header("HTTP/1.0 200 OK");
            return json_encode($data);
        }else{
            $data = [
                'status' => 405,
                'messange' =>  'No customer found',
            ];
            header("HTTP/1.0 405 Method not allowed");
            echo json_encode($data);
        }
    }else{
        $data = [
            'status' => 500,
            'messange' => 'Internal server error',
        ];
        header("HTTP/1.0 500 Internal server error");
        echo json_encode($data);
    }
}
function getPassengerAccountList(){

    global $conn;
    $query = "SELECT * FROM khachhang";
    $query_run = mysqli_query($conn,$query);

    if($query_run){

        if(mysqli_num_rows($query_run) > 0){

            $res = mysqli_fetch_all($query_run, MYSQLI_ASSOC);

            $data = [
                'status' => 200,
                'message' => 'Customer List Fetched Successfully',
                'data' => $res
            ];
            header("HTTP/1.0 200 OK");
            return json_encode($data);
        }else{
            $data = [
                'status' => 405,
                'messange' =>  'No customer found',
            ];
            header("HTTP/1.0 405 Method not allowed");
            echo json_encode($data);
        }
    }else{
        $data = [
            'status' => 500,
            'messange' => 'Internal server error',
        ];
        header("HTTP/1.0 500 Internal server error");
        echo json_encode($data);
    }
}
function getPassengerAccount($passengerParams){
    global $conn;
    if($passengerParams['maKH'] == null){
        return error422('Nhập mã khách hàng');
    }

    $passengerId = mysqli_real_escape_string($conn,$passengerParams['maKH']);
    $query = "SELECT * FROM khachhang WHERE maKH = '$passengerId' LIMIT 1";
    $result = mysqli_query($conn,$query);

    if($result){

        if(mysqli_num_rows($result) == 1){
            $res = mysqli_fetch_assoc($result);
            $data = [
                'status' => 200,
                'message' => 'Customer Fetched Successfully',
                'data' => $res
            ];
            header("HTTP/1.0 200 OK");
            return json_encode($data);
        }else{
            $data = [
                'status' => 404,
                'message' => 'Không có khách hàng nào được tìm thấy'
            ];
            header("HTTP/1.0 404 Internal server error");
            echo json_encode($data);
        }
    }else{
        $data = [
            'status' => 500,
            'messange' => 'Internal server error',
        ];
        header("HTTP/1.0 500 Internal server error");
        echo json_encode($data);
    }
}
function getPassenger($passengerParams){
    global $conn;
    if($passengerParams['maKH'] == null){
        return error422('Nhập mã khách hàng');
    }

    $passengerId = mysqli_real_escape_string($conn,$passengerParams['maKH']);
    $query = "SELECT * FROM khachhang WHERE maKH = '$passengerId' LIMIT 1";
    $result = mysqli_query($conn,$query);

    if($result){

        if(mysqli_num_rows($result) == 1){
            $res = mysqli_fetch_assoc($result);
            $data = [
                'status' => 200,
                'message' => 'Customer Fetched Successfully',
                'data' => $res
            ];
            header("HTTP/1.0 200 OK");
            return json_encode($data);
        }else{
            $data = [
                'status' => 404,
                'message' => 'Không có khách hàng nào được tìm thấy'
            ];
            header("HTTP/1.0 404 Internal server error");
            echo json_encode($data);
        }
    }else{
        $data = [
            'status' => 500,
            'messange' => 'Internal server error',
        ];
        header("HTTP/1.0 500 Internal server error");
        echo json_encode($data);
    }
}

function storePassenger($passengerInput){
    global $conn;

    $hoKH = mysqli_real_escape_string($conn, $passengerInput['hoKH']);
    $tenKH = mysqli_real_escape_string($conn, $passengerInput['tenKH']);
    $gioiTinh = mysqli_real_escape_string($conn, $passengerInput['gioiTinh']);
    $ngaySinh = mysqli_real_escape_string($conn, $passengerInput['ngaySinh']);
    $soCCCD = mysqli_real_escape_string($conn, $passengerInput['soCCCD']);
    $diaChi = mysqli_real_escape_string($conn, $passengerInput['diaChi']);
    $soDT = mysqli_real_escape_string($conn, $passengerInput['soDT']);
    $loaiHanhKhach = mysqli_real_escape_string($conn, $passengerInput['loaiHanhKhach']);


    if(empty(trim($hoKH))){
        return error422('Hãy nhập họ khách hàng');
    }
    elseif(empty(trim($tenKH))){
        return error422('Hãy nhập tên khách hàng');
    }
    elseif(empty(trim($gioiTinh))){
        return error422('Hãy nhập giới tính khách hàng');
    }
    elseif(empty(trim($ngaySinh))){
        return error422('Hãy nhập ngày sinh khách hàng');
    }
    elseif(empty(trim($soCCCD))){
        return error422('Hãy nhập CCCD khách hàng');
    }
    elseif(empty(trim($diaChi))){
        return error422('Hãy nhập địa chỉ khách hàng');
    }
    elseif(empty(trim($soDT))){
        return error422('Hãy nhập số điện thoại khách hàng');
    }
    elseif(empty(trim($loaiHanhKhach))){
        return error422('Hãy nhập loại hành khách');
    }
    else{
        $query = "INSERT INTO khachhang (hoKH, tenKH, gioiTinh, ngaySinh, soCCCD, diaChi, soDT, loaiHanhKhach)
        VALUES ('$hoKH','$tenKH','$gioiTinh','$ngaySinh','$soCCCD','$diaChi','$soDT','$loaiHanhKhach')";
        $result = mysqli_query($conn,$query);

        if($result){

            $data = [
                'status' => 201,
                'messange' => 'Khách hàng đã được thêm thành công',
            ];
            header("HTTP/1.0 201 Created");
            echo json_encode($data);

        }else{
            $data = [
                'status' => 500,
                'messange' => 'Internal server error',
            ];
            header("HTTP/1.0 500 Method not allowed");
            echo json_encode($data);
        }
    }

}

function deletePassenger($passengerParams){
    global $conn;

    if(!isset($passengerParams['maKH'])){
        return error422('Mã khách hàng không tìm thấy');
    }elseif($passengerParams['maKH'] == null){
        return error422('Nhập mã khách hàng');
    }

    $customerId = mysqli_real_escape_string($conn,$passengerParams['maKH']);

    $query = "DELETE FROM khachhang WHERE maKH = '$customerId' LIMIT 1";
    $result = mysqli_query($conn,$query);

    if($result){
        $data = [
            'status' => 204,
            'messange' => 'Xóa thành công',
        ];
        header("HTTP/1.0 204 Deleted");
        echo json_encode($data);
    }else{
        $data = [
            'status' => 404,
            'messange' => 'Không tìm thấy khách hàng',
        ];
        header("HTTP/1.0 404 Not Found");
        echo json_encode($data);
    }
}  

function updatePassenger($passengerInput, $passengerParams){
    global $conn;

    if(!isset($passengerParams['maKH'])){
        return error422('Mã khách hàng không tìm thấy');
    }elseif($passengerParams['maKH'] == null){
        return error422('Nhập mã khách hàng');
    }

    $maKH = mysqli_real_escape_string($conn,$passengerParams['maKH']);
    $fullname = mysqli_real_escape_string($conn, $passengerInput['fullname']);
    $email = mysqli_real_escape_string($conn, $passengerInput['email']);
    $gioiTinh = mysqli_real_escape_string($conn, $passengerInput['gioiTinh']);
    $ngaySinh = mysqli_real_escape_string($conn, $passengerInput['ngaySinh']);
    $soCCCD = mysqli_real_escape_string($conn, $passengerInput['soCCCD']);
    $diaChi = mysqli_real_escape_string($conn, $passengerInput['diaChi']);
    $soDT = mysqli_real_escape_string($conn, $passengerInput['soDT']);
    $loaiHanhKhach = mysqli_real_escape_string($conn, $passengerInput['loaiHanhKhach']);
        $query = "UPDATE khachhang SET fullname='$fullname',email = '$email', gioiTinh = '$gioiTinh', ngaySinh = '$ngaySinh',
        diaChi = '$diaChi', soDT = '$soDT' WHERE maKH = '$maKH' LIMIT 1";
        $result = mysqli_query($conn,$query);

        if($result){

            $data = [
                'status' => 200,
                'messange' => 'Khách hàng đã được sửa thành công',
            ];
            header("HTTP/1.0 200 Success");
            echo json_encode($data);

        }else{
            $data = [
                'status' => 500,
                'messange' => 'Internal server error',
            ];
            header("HTTP/1.0 500 Method not allowed");
            echo json_encode($data);
        }
}
   
    


//end passenger

//--------------------------------------Account passenger---------------------------------------------------
function storeUser($userInput){
    global $conn;

    $hoNV = mysqli_real_escape_string($conn, $userInput['hoNV']);
    $tenNV = mysqli_real_escape_string($conn, $userInput['tenNV']);
    // $ngaySinhNV = mysqli_real_escape_string($conn, $userInput['ngaySinhNV']);
    // $sdtNV = mysqli_real_escape_string($conn, $userInput['sdtNV']);
    $chucVu = mysqli_real_escape_string($conn, $userInput['chucVu']);
    $trinhDoHocVan = mysqli_real_escape_string($conn, $userInput['trinhDoHocVan']);
    $kinhNghiem = mysqli_real_escape_string($conn, $userInput['kinhNghiem']);
    // $trangThaiHoatDong = mysqli_real_escape_string($conn, $userInput['trangThaiHoatDong']);
    $username = mysqli_real_escape_string($conn, $userInput['username']);
    $passw = mysqli_real_escape_string($conn, $userInput['passw']);
    $salt = bin2hex(random_bytes(22));
    $hashedPassword = password_hash($passw,  PASSWORD_BCRYPT);
    // Kiểm tra trùng lặp
    $duplicateQuery = "SELECT COUNT(*) AS count FROM nhanvien 
                   WHERE hoNV = '$hoNV' 
                   AND tenNV = '$tenNV' 
                   AND chucVu = '$chucVu'
                   AND trinhDoHocVan = '$trinhDoHocVan'
                   AND username = '$username'
                   AND kinhNghiem = '$kinhNghiem'
                   AND passw = '$passw'";
    $duplicateResult = mysqli_query($conn, $duplicateQuery);
    if ($duplicateResult) {
        $duplicateRow = mysqli_fetch_assoc($duplicateResult);
        $duplicateCount = $duplicateRow['count'];

        if ($duplicateCount > 0) {
            $data = [
                'status' => 400,
                'message' => 'Đã có chuyến bay này. Vui lòng kiểm tra lại.',
            ];
            header("HTTP/1.0 400 Bad Request");
            echo json_encode($data);
            return;
        }
    } else {
        $data = [
            'status' => 500,
            'message' => 'Internal server error during duplicate check.',
        ];
        header("HTTP/1.0 500 Internal Server Error");
        echo json_encode($data);
        return;
    }
    // echo "Mật khẩu đã băm: " . $hashedPassword;
    if(empty(trim($hoNV))){
        return error422('Hãy nhập họ nhân viên');
    }
    elseif(empty(trim($tenNV))){
        return error422('Hãy nhập tên nhân viên');
    }
    elseif(empty(trim($chucVu))){
        return error422('Hãy nhập chức vụ nhân viên');
    }
    elseif(empty(trim($trinhDoHocVan))){
        return error422('Hãy nhập trình độ học vấn nhân viên');
    }
    elseif(empty(trim($kinhNghiem))){
        return error422('Hãy nhập kinh nghiệm nhân viên');
    }elseif(empty(trim($username))){
        return error422('Hãy nhập tài khoản nhân viên');
    }
    elseif(empty(trim($passw))){
        return error422('Hãy nhập mật khẩu cho nhân viên');
    }
    else{
        $query = "INSERT INTO nhanvien (hoNV,tenNV,chucVu,trinhDoHocVan,kinhNghiem,username,passw,salt)
         VALUES ('$hoNV','$tenNV','$chucVu','$trinhDoHocVan','$kinhNghiem','$username','$hashedPassword','$salt')";
        $result = mysqli_query($conn,$query);

        if($result){

            $data = [
                'status' => 201,
                'messange' => 'Nhân viên đã được thêm thành công',
            ];
            header("HTTP/1.0 201 Created");
            echo json_encode($data);

        }else{
            $data = [
                'status' => 500,
                'messange' => 'Internal server error',
            ];
            header("HTTP/1.0 500 Method not allowed");
            echo json_encode($data);
        }
    }

}
function getUserList(){

    global $conn;
    $query = "SELECT * FROM nhanvien";
    $query_run = mysqli_query($conn,$query);

    if($query_run){

        if(mysqli_num_rows($query_run) > 0){

            $res = mysqli_fetch_all($query_run, MYSQLI_ASSOC);

            $data = [
                'status' => 200,
                'message' => 'Customer List Fetched Successfully',
                'data' => $res
            ];
            header("HTTP/1.0 200 OK");
            return json_encode($data);
        }else{
            $data = [
                'status' => 405,
                'messange' =>  'No airline found',
            ];
            header("HTTP/1.0 405 Method not allowed");
            echo json_encode($data);
        }
    }else{
        $data = [
            'status' => 500,
            'messange' => 'Internal server error',
        ];
        header("HTTP/1.0 500 Internal server error");
        echo json_encode($data);
    }
}

function getUser($userParams){
    global $conn;
    if($userParams['maNV'] == null){
        return error422('Nhập mã nhân viên');
    }

    $userId = mysqli_real_escape_string($conn,$userParams['maNV']);
    $query = "SELECT * FROM nhanvien WHERE maNV = '$userId' LIMIT 1";
    $result = mysqli_query($conn,$query);

    if($result){

        if(mysqli_num_rows($result) == 1){
            $res = mysqli_fetch_assoc($result);
            $data = [
                'status' => 200,
                'message' => 'User Fetched Successfully',
                'data' => $res
            ];
            header("HTTP/1.0 200 OK");
            return json_encode($data);
        }else{
            $data = [
                'status' => 404,
                'message' => 'Không có nhân viên nào được tìm thấy'
            ];
            header("HTTP/1.0 404 Internal server error");
            echo json_encode($data);
        }
    }else{
        $data = [
            'status' => 500,
            'messange' => 'Internal server error',
        ];
        header("HTTP/1.0 500 Internal server error");
        echo json_encode($data);
    }
}

function updateUser($userInput, $userParams){
    global $conn;

    if(!isset($userParams['maNV'])){
        return error422('Mã nhân viên không tìm thấy');
    }elseif($userParams['maNV'] == null){
        return error422('Nhập mã nhân viên');
    }
    
    $maNV = mysqli_real_escape_string($conn, $userParams['maNV']);
    $hoNV = mysqli_real_escape_string($conn, $userInput['hoNV']);
    $tenNV = mysqli_real_escape_string($conn, $userInput['tenNV']);
    $ngaySinhNV = mysqli_real_escape_string($conn, $userInput['ngaySinhNV']);
    $sdtNV = mysqli_real_escape_string($conn, $userInput['sdtNV']);
    $chucVu = mysqli_real_escape_string($conn, $userInput['chucVu']);
    $trinhDoHocVan = mysqli_real_escape_string($conn, $userInput['trinhDoHocVan']);
    $kinhNghiem = mysqli_real_escape_string($conn, $userInput['kinhNghiem']);
    $trangThaiHoatDong = mysqli_real_escape_string($conn, $userInput['trangThaiHoatDong']);
    $username = mysqli_real_escape_string($conn, $userInput['username']);
    $passw = mysqli_real_escape_string($conn, $userInput['passw']);

    if(empty(trim($hoNV))){
        return error422('Hãy nhập họ nhân viên');
    }
    elseif(empty(trim($tenNV))){
        return error422('Hãy nhập tên nhân viên');
    }
    elseif(empty(trim($username))){
        return error422('Hãy nhập tài khoản nhân viên');
    }
    elseif(empty(trim($passw))){
        return error422('Hãy nhập mật khẩu cho nhân viên');
    }
    elseif(empty(trim($chucVu))){
        return error422('Hãy nhập chức vụ nhân viên');
    }
    elseif(empty(trim($trinhDoHocVan))){
        return error422('Hãy nhập trình độ học vấn nhân viên');
    }
    elseif(empty(trim($kinhNghiem))){
        return error422('Hãy nhập kinh nghiệm nhân viên');
    }
    // elseif(empty(trim($trangThaiHoatDong))){
    //     return error422('Hãy nhập trạng thái hoạt động nhân viên');
    // }
    else{
        $query = "UPDATE nhanvien SET hoNV='$hoNV', tenNV = '$tenNV',username = '$username',
        passw='$passw',chucVu ='$chucVu',trinhDoHocVan = '$trinhDoHocVan',kinhNghiem = '$kinhNghiem',trangThaiHoatDong = '$trangThaiHoatDong'
        WHERE maNV = '$maNV' LIMIT 1";
        $result = mysqli_query($conn,$query);

        if($result){

            $data = [
                'status' => 200,
                'messange' => 'Nhân viên đã được sửa thành công',
            ];
            header("HTTP/1.0 200 Success");
            echo json_encode($data);

        }else{
            $data = [
                'status' => 500,
                'messange' => 'Internal server error',
            ];
            header("HTTP/1.0 500 Method not allowed");
            echo json_encode($data);
        }
    }
}


function deleteUser($userParams){
    global $conn;

    if(!isset($userParams['maNV'])){
        return error422('Mã nhân viên không tìm thấy');
    }elseif($userParams['maNV'] == null){
        return error422('Nhập mã nhân viên');
    }

    $userId = mysqli_real_escape_string($conn,$userParams['maNV']);

    $query = "DELETE FROM nhanvien WHERE maNV = '$userId' LIMIT 1";
    $result = mysqli_query($conn,$query);

    if($result){
        $data = [
            'status' => 204,
            'messange' => 'Xóa thành công',
        ];
        header("HTTP/1.0 204 Deleted");
        echo json_encode($data);
    }else{
        $data = [
            'status' => 404,
            'messange' => 'Không tìm thấy nhân viên',
        ];
        header("HTTP/1.0 404 Not Found");
        echo json_encode($data);
    }
} 


//------------------------------------------flights------------------------------------
function getFlightList(){

    global $conn;
    $query = "SELECT * FROM thongtinchuyenbay";
    $query_run = mysqli_query($conn,$query);

    if($query_run){

        if(mysqli_num_rows($query_run) > 0){

            $res = mysqli_fetch_all($query_run, MYSQLI_ASSOC);

            $data = [
                'status' => 200,
                'message' => 'Flight List Fetched Successfully',
                'data' => $res
            ];
            header("HTTP/1.0 200 OK");
            return json_encode($data);
        }else{
            $data = [
                'status' => 405,
                'messange' =>  'No flight found',
            ];
            header("HTTP/1.0 405 Method not allowed");
            echo json_encode($data);
        }
    }else{
        $data = [
            'status' => 500,
            'messange' => 'Internal server error',
        ];
        header("HTTP/1.0 500 Internal server error");
        echo json_encode($data);
    }
}

function getFlight($flightParams){
    global $conn;
    if($flightParams['maCB'] == null){
        return error422('Nhập mã chuyến bay');
    }

    $flightId = mysqli_real_escape_string($conn,$flightParams['maCB']);
    $query = "SELECT * FROM thongtinchuyenbay WHERE maCB = '$flightId' LIMIT 1";
    $result = mysqli_query($conn,$query);

    if($result){

        if(mysqli_num_rows($result) == 1){
            $res = mysqli_fetch_assoc($result);
            $data = [
                'status' => 200,
                'message' => 'Flight Fetched Successfully',
                'data' => $res
            ];
            header("HTTP/1.0 200 OK");
            return json_encode($data);
        }else{
            $data = [
                'status' => 404,
                'message' => 'Không có chuyến bay nào được tìm thấy'
            ];
            header("HTTP/1.0 404 Internal server error");
            echo json_encode($data);
        }
    }else{
        $data = [
            'status' => 500,
            'messange' => 'Internal server error',
        ];
        header("HTTP/1.0 500 Internal server error");
        echo json_encode($data);
    }
}

function storeFlight($flightInput){
    global $conn;

    //$maDB = mysqli_real_escape_string($conn, $flightInput['maDB']);
    $maMB = mysqli_real_escape_string($conn, $flightInput['maMB']);
    $ngayDen = mysqli_real_escape_string($conn, $flightInput['ngayDen']);
    $ngayDi = mysqli_real_escape_string($conn, $flightInput['ngayDi']);
    $diaDiemDen = mysqli_real_escape_string($conn, $flightInput['diaDiemDen']);
    $diaDiemDi = mysqli_real_escape_string($conn, $flightInput['diaDiemDi']);
    $giaVe = mysqli_real_escape_string($conn, $flightInput['giaVe']);
    $ghiChu = mysqli_real_escape_string($conn, $flightInput['ghiChu']);
    $gioBay = mysqli_real_escape_string($conn, $flightInput['gioBay']);

    // Kiểm tra trùng lặp
    $duplicateQuery = "SELECT COUNT(*) AS count FROM thongtinchuyenbay 
                   WHERE maMB = '$maMB' 
                   AND ngayDi = '$ngayDi' 
                   AND ngayDen = '$ngayDen'
                   AND diaDiemDen = '$diaDiemDen'
                   AND diaDiemDi = '$diaDiemDi'
                   AND giaVe = '$giaVe'
                   AND ghiChu = '$ghiChu'
                   AND gioBay = '$gioBay'";
    $duplicateResult = mysqli_query($conn, $duplicateQuery);

    if ($duplicateResult) {
        $duplicateRow = mysqli_fetch_assoc($duplicateResult);
        $duplicateCount = $duplicateRow['count'];

        if ($duplicateCount > 0) {
            $data = [
                'status' => 400,
                'message' => 'Đã có chuyến bay này. Vui lòng kiểm tra lại.',
            ];
            header("HTTP/1.0 400 Bad Request");
            echo json_encode($data);
            return;
        }
    } else {
        $data = [
            'status' => 500,
            'message' => 'Internal server error during duplicate check.',
        ];
        header("HTTP/1.0 500 Internal Server Error");
        echo json_encode($data);
        return;
    }

    // if(empty(trim($maDB))){
    //     return error422('Hãy nhập mã đường bay');
    // }
    if(empty(trim($maMB))){
        return error422('Hãy nhập mã máy bay');
    }
    elseif(empty(trim($ngayDen))){
        return error422('Hãy nhập ngày đến');
    }
    elseif(empty(trim($ngayDi))){
        return error422('Hãy nhập ngày đi');
    }
    elseif(empty(trim($diaDiemDen))){
        return error422('Hãy nhập địa điểm đến');
    }
    elseif(empty(trim($diaDiemDi))){
        return error422('Hãy nhập địa điểm đi');
    }
    elseif(empty(trim($giaVe))){
        return error422('Hãy nhập giá vé');
    }
    elseif(empty(trim($ghiChu))){
        return error422('Hãy nhập ghi chú');
    }
    elseif(empty(trim($gioBay))){
        return error422('Hãy nhập giờ bay');
    }
    else{
        $query = "INSERT INTO thongtinchuyenbay (maMB,ngayDen,ngayDi,diaDiemDen,diaDiemDi,giaVe,ghiChu,gioBay)
         VALUES ('$maMB','$ngayDen','$ngayDi','$diaDiemDen','$diaDiemDi','$giaVe','$ghiChu','$gioBay')";
        $result = mysqli_query($conn,$query);

        if($result){

            $data = [
                'status' => 201,
                'messange' => 'Chuyến bay đã được thêm thành công',
            ];
            header("HTTP/1.0 201 Created");
            echo json_encode($data);

        }else{
            $data = [
                'status' => 500,
                'messange' => 'Internal server error',
            ];
            header("HTTP/1.0 500 Method not allowed");
            echo json_encode($data);
        }
    }

}

function deleteFlight($flightParams){
    global $conn;

    if(!isset($flightParams['maCB'])){
        return error422('Mã chuyến bay không tìm thấy');
    }elseif($flightParams['maCB'] == null){
        return error422('Nhập mã chuyến bay');
    }

    $flightId = mysqli_real_escape_string($conn,$flightParams['maCB']);

    $query = "DELETE FROM thongtinchuyenbay WHERE maCB = '$flightId' LIMIT 1";
    $result = mysqli_query($conn,$query);

    if($result){
        $data = [
            'status' => 204,
            'messange' => 'Xóa thành công',
        ];
        header("HTTP/1.1 204 No Content"); 
        echo json_encode($data);
    }else{
        $data = [
            'status' => 404,
            'messange' => 'Không tìm thấy chuyến bay',
        ];
        header("HTTP/1.1 404 Not Found"); 
        echo json_encode($data);
    }
}  

function updateFlight($flightInput, $flightParams){
    global $conn;

    if(!isset($flightParams['maCB'])){
        return error422('Mã chuyến bay không tìm thấy');
    }elseif($flightParams['maCB'] == null){
        return error422('Nhập mã chuyến bay');
    }

    $flightId = mysqli_real_escape_string($conn,$flightParams['maCB']);
  //  $maDB = mysqli_real_escape_string($conn, $_POST['maDB']);
    $maMB = mysqli_real_escape_string($conn, $flightInput['maMB']);
    $ngayDen = mysqli_real_escape_string($conn, $flightInput['ngayDen']);
    $ngayDi = mysqli_real_escape_string($conn, $flightInput['ngayDi']);
    $diaDiemDen = mysqli_real_escape_string($conn, $flightInput['diaDiemDen']);
    $diaDiemDi = mysqli_real_escape_string($conn, $flightInput['diaDiemDi']);
    $giaVe = mysqli_real_escape_string($conn, $flightInput['giaVe']);
    $ghiChu = mysqli_real_escape_string($conn, $flightInput['ghiChu']);
    $gioBay = mysqli_real_escape_string($conn, $flightInput['gioBay']);
    
    if(empty(trim($ngayDen))){
        return error422('Hãy nhập ngày đến');
    }
    elseif(empty(trim($ngayDi))){
        return error422('Hãy nhập ngày đi');
    }
    elseif(empty(trim($diaDiemDen))){
        return error422('Hãy nhập địa điểm đến');
    }
    elseif(empty(trim($diaDiemDi))){
        return error422('Hãy nhập địa điểm đi');
    }
    elseif(empty(trim($giaVe))){
        return error422('Hãy nhập giá vé');
    }
    elseif(empty(trim($ghiChu))){
        return error422('Hãy nhập ghi chú');
    }
    elseif(empty(trim($gioBay))){
        return error422('Hãy nhập giờ bay');
    }
    else{
        $query = "UPDATE thongtinchuyenbay SET maMB = '$maMB', ngayDen = '$ngayDen', ngayDi = '$ngayDi',
        diaDiemDen = '$diaDiemDen', diaDiemDi = '$diaDiemDi', giaVe = '$giaVe', ghiChu = '$ghiChu', gioBay='$gioBay' 
        WHERE maCB = '$flightId' LIMIT 1";
        $result = mysqli_query($conn,$query);

        if($result){

            $data = [
                'status' => 200,
                'messange' => 'Chuyến bay đã được sửa thành công',
            ];
            header("HTTP/1.0 200 Success");
            echo json_encode($data);

        }else{
            $data = [
                'status' => 500,
                'messange' => 'Internal server error',
            ];
            header("HTTP/1.0 500 Method not allowed");
            echo json_encode($data);
        }
    }

}

//------------------------Ve------------------------------------------------------------------------------
function getTicketList(){

    global $conn;
    $query = "SELECT * FROM ve";
    $query_run = mysqli_query($conn,$query);

    if($query_run){

        if(mysqli_num_rows($query_run) > 0){

            $res = mysqli_fetch_all($query_run, MYSQLI_ASSOC);

            $data = [
                'status' => 200,
                'message' => 'Ticket List Fetched Successfully',
                'data' => $res
            ];
            header("HTTP/1.0 200 OK");
            return json_encode($data);
        }else{
            $data = [
                'status' => 405,
                'messange' =>  'No ticket found',
            ];
            header("HTTP/1.0 405 Method not allowed");
            echo json_encode($data);
        }
    }else{
        $data = [
            'status' => 500,
            'messange' => 'Internal server error',
        ];
        header("HTTP/1.0 500 Internal server error");
        echo json_encode($data);
    }
}

function getTicket($ticketParams){
    global $conn;
    if($ticketParams['maVe'] == null){
        return error422('Nhập mã vé');
    }

    $ticketId = mysqli_real_escape_string($conn,$ticketParams['maVe']);
    $query = "SELECT * FROM ve WHERE maVe = '$ticketId' LIMIT 1";
    $result = mysqli_query($conn,$query);

    if($result){

        if(mysqli_num_rows($result) == 1){
            $res = mysqli_fetch_assoc($result);
            $data = [
                'status' => 200,
                'message' => 'Ticket Fetched Successfully',
                'data' => $res
            ];
            header("HTTP/1.0 200 OK");
            return json_encode($data);
        }else{
            $data = [
                'status' => 404,
                'message' => 'Không có vé nào được tìm thấy'
            ];
            header("HTTP/1.0 404 Internal server error");
            echo json_encode($data);
        }
    }else{
        $data = [
            'status' => 500,
            'messange' => 'Internal server error',
        ];
        header("HTTP/1.0 500 Internal server error");
        echo json_encode($data);
    }
}

function storeTicket($ticketInput){
    global $conn;
    $maVe= mysqli_real_escape_string($conn, $ticketInput['maVe']);
	$soLuong= mysqli_real_escape_string($conn, $ticketInput['soLuong']);
    $hangVe= mysqli_real_escape_string($conn, $ticketInput['hangVe']);
    // Kiểm tra trùng lặp
    $duplicateQuery = "SELECT COUNT(*) AS count FROM ve 
                   WHERE maVe = '$maVe' 
                   OR soLuong = '$soLuong' 
                   OR hangVe = '$hangVe'";
    $duplicateResult = mysqli_query($conn, $duplicateQuery);
    if ($duplicateResult) {
        $duplicateRow = mysqli_fetch_assoc($duplicateResult);
        $duplicateCount = $duplicateRow['count'];

        if ($duplicateCount > 0) {
            $data = [
                'status' => 400,
                'message' => 'Đã có vé này. Vui lòng kiểm tra lại.',
            ];
            header("HTTP/1.0 400 Bad Request");
            echo json_encode($data);
            return;
        }
    } else {
        $data = [
            'status' => 500,
            'message' => 'Internal server error during duplicate check.',
        ];
        header("HTTP/1.0 500 Internal Server Error");
        echo json_encode($data);
        return;
    }

    if(empty(trim($maVe))){
        return error422('Hãy nhập mã vé');}
    elseif(empty(trim($soLuong))){
        return error422('Hãy nhập số lượng');
    }elseif(empty(trim($hangVe))){
        return error422('Hãy nhập hạng vé');
    }
    else{
        $query = "INSERT INTO ve(maVe, soLuong, hangVe)
	VALUES ('$maVe','$soLuong','$hangVe' )";
        $result = mysqli_query($conn,$query);

        if($result){

            $data = [
                'status' => 201,
                'messange' => 'Vé đã được thêm thành công',
            ];
            header("HTTP/1.0 201 Created");
            echo json_encode($data);

        }else{
            $data = [
                'status' => 500,
                'messange' => 'Internal server error',
            ];
            header("HTTP/1.0 500 Method not allowed");
            echo json_encode($data);
        }
    }

}
function deleteTicket($ticketParams){
    global $conn;

    if(!isset($ticketParams['maVe'])){
        return error422('Mã vé không tìm thấy');
    }elseif($ticketParams['maVe'] == null){
        return error422('Nhập mã vé');
    }

    $ticketId = mysqli_real_escape_string($conn,$ticketParams['maVe']);

    $query = "DELETE FROM ve WHERE maVe = '$ticketId' LIMIT 1";
    $result = mysqli_query($conn,$query);

    if($result){
        $data = [
            'status' => 204,
            'messange' => 'Xóa thành công',
        ];
        header("HTTP/1.0 204 Deleted");
        echo json_encode($data);
    }else{
        $data = [
            'status' => 404,
            'messange' => 'Không tìm thấy chuyến bay',
        ];
        header("HTTP/1.0 404 Not Found");
        echo json_encode($data);
    }
}  

function updateTicket($ticketInput, $ticketParams){
    global $conn;

    if(!isset($ticketParams['maVe'])){
        return error422('Mã vé không tìm thấy');
    }elseif($ticketParams['maVe'] == null){
        return error422('Nhập mã vé');
    }
    $maVe = mysqli_real_escape_string($conn,$ticketParams['maVe']);
    // $maCB = mysqli_real_escape_string($conn, $_POST['maCB']);
    // $loaiVe = mysqli_real_escape_string($conn, $_POST['loaiVe']);
    $soLuong = mysqli_real_escape_string($conn, $ticketInput['soLuong']);
    // $soLuongCon = mysqli_real_escape_string($conn, $_POST['soLuongCon']);
    $hangVe = mysqli_real_escape_string($conn, $ticketInput['hangVe']);
    // $giaHangVe = mysqli_real_escape_string($conn, $_POST['giaHangVe']);

    // if(empty(trim($maCB))){
    //     return error422('Hãy nhập mã chuyến bay');
    // }
    // elseif(empty(trim($loaiVe))){
    //     return error422('Hãy nhập loại vé');}
    
    if(empty(trim($soLuong))){
        return error422('Hãy nhập số lượng');
    }
    // elseif(empty(trim($soLuongCon))){
    //     return error422('Hãy nhập số lượng còn');
    // }
    elseif(empty(trim($hangVe))){
        return error422('Hãy nhập hạng vé');
    }
    else{
        $query = "UPDATE ve SET soLuong = '$soLuong',
        hangVe = '$hangVe'
        WHERE maVe = '$maVe' LIMIT 1";
        $result = mysqli_query($conn,$query);

        if($result){

            $data = [
                'status' => 200,
                'messange' => 'Vé đã được sửa thành công',
            ];
            header("HTTP/1.0 200 Success");
            echo json_encode($data);

        }else{
            $data = [
                'status' => 500,
                'messange' => 'Internal server error',
            ];
            header("HTTP/1.0 500 Method not allowed");
            echo json_encode($data);
        }
    }

}

//----------------------------------------------chi tiết khách hàng---------------------------------------------------
function getDetailPassenger($detailParams){
    global $conn;
    if($detailParams['maVe'] == null){
        return error422('Nhập mã vé');
    }

    $detailId = mysqli_real_escape_string($conn,$detailParams['maKH']);
    $query = "SELECT * FROM veDaDat as a, thongtinchuyenbay as b, khachhang as c, ve as d
    WHERE a.maCB = b.maCB and a.maKH=c.maKH and a.maVe = d.maVe and a.maVe = '$detailId' LIMIT 1";
    $result = mysqli_query($conn,$query);

    if($result){

        if(mysqli_num_rows($result) == 1){
            $res = mysqli_fetch_assoc($result);
            $data = [
                'status' => 200,
                'message' => 'Customer Fetched Successfully',
                'data' => $res
            ];
            header("HTTP/1.0 200 OK");
            return json_encode($data);
        }else{
            $data = [
                'status' => 404,
                'message' => 'Không có khách hàng nào được tìm thấy'
            ];
            header("HTTP/1.0 404 Internal server error");
            echo json_encode($data);
        }
    }else{
        $data = [
            'status' => 500,
            'messange' => 'Internal server error',
        ];
        header("HTTP/1.0 500 Internal server error");
        echo json_encode($data);
    }
}

//-----------------------DetailTicket-------------------------------------------------
function getDetailTicketList(){

    global $conn;
    $query = "SELECT * FROM khachhang as a, thongtinchuyenbay as b , ve as c , vedadat as d
    WHERE a.maKH = d.maKH and b.maCB = d.maCB and c.maVe = d.maVe";
    $query_run = mysqli_query($conn,$query);
    
    if($query_run){
    
        if(mysqli_num_rows($query_run) > 0){
    
            $res = mysqli_fetch_all($query_run, MYSQLI_ASSOC);
    
            $data = [
                'status' => 200,
                'message' => 'Ticket List Fetched Successfully',
                'data' => $res
            ];
            header("HTTP/1.0 200 OK");
            return json_encode($data);
        }else{
            $data = [
                'status' => 405,
                'messange' =>  'No ticket found',
            ];
            header("HTTP/1.0 405 Method not allowed");
            echo json_encode($data);
        }
    }else{
        $data = [
            'status' => 500,
            'messange' => 'Internal server error',
        ];
        header("HTTP/1.0 500 Internal server error");
        echo json_encode($data);
    }
    }
    
function getDetailTicket($detailTicketParams){
    global $conn;
    if($detailTicketParams['maVe'] == null){
        return error422('Nhập mã vé');
    }
    
    $ticketId = mysqli_real_escape_string($conn,$detailTicketParams['maVe']);
    $query = "SELECT * FROM khachhang as a, thongtinchuyenbay as b , ve as c , vedadat as d
     WHERE maVe = '$ticketId' and a.maKH = d.maKH and b.maCB = d.maCB and c.maVe = d.maVe LIMIT 1";
    $result = mysqli_query($conn,$query);
    
    if($result){
    
        if(mysqli_num_rows($result) == 1){
            $res = mysqli_fetch_assoc($result);
            $data = [
                'status' => 200,
                'message' => 'Ticket Fetched Successfully',
                'data' => $res
            ];
            header("HTTP/1.0 200 OK");
            return json_encode($data);
        }else{
            $data = [
                'status' => 404,
                'message' => 'Không có vé nào được tìm thấy'
            ];
            header("HTTP/1.0 404 Internal server error");
            echo json_encode($data);
        }
    }else{
        $data = [
            'status' => 500,
            'messange' => 'Internal server error',
        ];
        header("HTTP/1.0 500 Internal server error");
        echo json_encode($data);
    }
    }
// ---------------------------------------------------Create Detail Ticket    ---------------------------------------------------
function storeDetailTicket($detailInput){
    global $conn;
    $order_id = mysqli_real_escape_string($conn, $detailInput['order_id']);
    $maVe = mysqli_real_escape_string($conn, $detailInput['maVe']);
    $maCB = mysqli_real_escape_string($conn, $detailInput['maCB']);
    $maKH = mysqli_real_escape_string($conn, $detailInput['maKH']);
    $soLuongDat = mysqli_real_escape_string($conn, $detailInput['soLuongDat']);
    $tongThanhToan = mysqli_real_escape_string($conn, $detailInput['tongThanhToan']);

    if(empty(trim($maVe))){
        return error422('Hãy nhập mã Vé');
    }
    elseif(empty(trim($maCB))){
        return error422('Hãy nhập mã chuyến bay');
    }
    elseif(empty(trim($maKH))){
        return error422('Hãy nhập mã khách hàng');
    }
    elseif(empty(trim($soLuongDat))){
        return error422('Hãy nhập số lượng đặt');
    }
    else{
        $query = "INSERT INTO vedadat (order_id,maVe,maCB,maKH,soLuongDat,tongThanhToan)
         VALUES ('$order_id','$maVe','$maCB','$maKH','$soLuongDat','$tongThanhToan')";
        $result = mysqli_query($conn,$query);

        if($result){

            $data = [
                'status' => 201,
                'messange' => 'Đặt vé thành công',
            ];
            header("HTTP/1.0 201 Created");
            echo json_encode($data);

        }else{
            $data = [
                'status' => 500,
                'messange' => 'Internal server error',
            ];
            header("HTTP/1.0 500 Method not allowed");
            echo json_encode($data);
        }
    }

}
function storeMess($messInput){
    global $conn;

    $noiDung1 = mysqli_real_escape_string($conn, $messInput['noiDung1']);
    $thoiGianGui = mysqli_real_escape_string($conn, $messInput['thoiGianGui']);
    $maKH = mysqli_real_escape_string($conn, $messInput['maKH']);
    $noiDung2 = mysqli_real_escape_string($conn, $messInput['noiDung2']);

    if(empty(trim($thoiGianGui))){
        return error422('Hãy nhập thời gian gửi');
    }
    elseif(empty(trim($maKH))){
        return error422('Hãy nhập mã khách hàng');
    }
    else{
        $query = "INSERT INTO tinnhan (noiDung1,thoiGianGui,maKH,noiDung2) VALUES ('$noiDung1','$thoiGianGui','$maKH','$noiDung2')";
        $result = mysqli_query($conn,$query);

        if($result){

            $data = [
                'status' => 201,
                'messange' => 'Tin nhắn đã được thêm thành công',
            ];
            header("HTTP/1.0 201 Created");
            echo json_encode($data);

        }else{
            $data = [
                'status' => 500,
                'messange' => 'Internal server error',
            ];
            header("HTTP/1.0 500 Method not allowed");
            echo json_encode($data);
        }
    }

}
function getMessList(){

    global $conn;
    $query = "SELECT * FROM khachhang as a, tinnhan as b
    WHERE a.maKH = b.maKH";
    $query_run = mysqli_query($conn,$query);

    if($query_run){

        if(mysqli_num_rows($query_run) > 0){

            $res = mysqli_fetch_all($query_run, MYSQLI_ASSOC);

            $data = [
                'status' => 200,
                'message' => 'Messenger List Fetched Successfully',
                'data' => $res
            ];
            header("HTTP/1.0 200 OK");
            return json_encode($data);
        }else{
            $data = [
                'status' => 405,
                'messange' =>  'No messenger found',
            ];
            header("HTTP/1.0 405 Method not allowed");
            echo json_encode($data);
        }
    }else{
        $data = [
            'status' => 500,
            'messange' => 'Internal server error',
        ];
        header("HTTP/1.0 500 Internal server error");
        echo json_encode($data);
    }
}

function getMess($messParams){
    global $conn;

    if ($messParams['maKH'] == null){
        return error422('Nhập mã khách hàng');
    }

    $passengerId = mysqli_real_escape_string($conn, $messParams['maKH']);
    $query = "SELECT * FROM tinnhan WHERE maKH = '$passengerId'";
    $result = mysqli_query($conn, $query);

    if ($result){
        $messArray = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $messArray[] = $row;
        }

        if (!empty($messArray)){
            $data = [
                'status' => 200,
                'message' => 'Mess Fetched Successfully',
                'data' => $messArray
            ];
            header("HTTP/1.0 200 OK");
            echo json_encode($data);
        }else{
            $data = [
                'status' => 404,
                'message' => 'Không có tin nhắn nào được tìm thấy'
            ];
            header("HTTP/1.0 404 Not Found");
            echo json_encode($data);
        }
    }else{
        $data = [
            'status' => 500,
            'message' => 'Internal server error',
        ];
        header("HTTP/1.0 500 Internal Server Error");
        echo json_encode($data);
    }
}
function getChat($chatParams){
    global $conn;
    if($chatParams['maKH'] == null){
        return error422('Nhập mã KH');
    }
    
    $userId = mysqli_real_escape_string($conn,$chatParams['maKH']);
    $query = "SELECT DISTINCT a.fullname, b.maKH FROM khachhang as a, tinnhan as b
    WHERE a.maKH = b.maKH";
    $result = mysqli_query($conn,$query);
    
    if($result){
    
        if(mysqli_num_rows($result) == 1){
            $res = mysqli_fetch_assoc($result);
            $data = [
                'status' => 200,
                'message' => 'User Fetched Successfully',
                'data' => $res
            ];
            header("HTTP/1.0 200 OK");
            return json_encode($data);
        }else{
            $data = [
                'status' => 404,
                'message' => 'Không có khách hàng nào được tìm thấy'
            ];
            header("HTTP/1.0 404 Internal server error");
            echo json_encode($data);
        }
    }else{
        $data = [
            'status' => 500,
            'messange' => 'Internal server error',
        ];
        header("HTTP/1.0 500 Internal server error");
        echo json_encode($data);
    }
    }
    function getChatList(){

        global $conn;
        $query = "SELECT DISTINCT a.fullname, b.maKH FROM khachhang as a, tinnhan as b
        WHERE a.maKH = b.maKH";
        $query_run = mysqli_query($conn,$query);
        
        if($query_run){
        
            if(mysqli_num_rows($query_run) > 0){
        
                $res = mysqli_fetch_all($query_run, MYSQLI_ASSOC);
        
                $data = [
                    'status' => 200,
                    'message' => 'Chat List Fetched Successfully',
                    'data' => $res
                ];
                header("HTTP/1.0 200 OK");
                return json_encode($data);
            }else{
                $data = [
                    'status' => 405,
                    'messange' =>  'No ticket found',
                ];
                header("HTTP/1.0 405 Method not allowed");
                echo json_encode($data);
            }
        }else{
            $data = [
                'status' => 500,
                'messange' => 'Internal server error',
            ];
            header("HTTP/1.0 500 Internal server error");
            echo json_encode($data);
        }
        }
        function logInUser($accountInput){
            global $conn;
            $username = mysqli_real_escape_string($conn, $accountInput['username']);
            $passw = mysqli_real_escape_string($conn, $accountInput['passw']);
        
            if(empty(trim($username)) || empty(trim($passw))){
                return error422('Hãy nhập username và mật khẩu');
            } else {
                // Sử dụng Prepared Statements để ngăn chặn SQL Injection
                $query = "SELECT * FROM `nhanvien` WHERE username = ? LIMIT 1";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, "s", $username);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
        
                if(mysqli_num_rows($result) != 0 ){
                    $row = mysqli_fetch_assoc($result);
                    $dbusername = $row['username'];
                    $dbpassword = $row['passw'];
        
                    // Kiểm tra mật khẩu sử dụng password_verify
                    if ($dbusername == $username && password_verify($passw, $dbpassword)) {
                        $login = true;
                    } else {
                        $login = false;
                    }
                } else {
                    $login = false;
                }
        
                return $login;
            }
        }
?>

