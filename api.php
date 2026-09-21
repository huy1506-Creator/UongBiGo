<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

$host = '127.0.0.1';
$dbname = 'uongbigo';
$dbuser = 'root';
$dbpass = '';

function json_input() {
    $raw = file_get_contents('php://input');
    if (!$raw) return [];
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}
function out($data, $status=200) {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}
function fail_api($status, $message) { out(['message'=>$message], $status); }

try {
    $pdo = new PDO(
        "mysql:host=$GLOBALS[host];dbname=$GLOBALS[dbname];charset=utf8mb4",
        $GLOBALS['dbuser'], $GLOBALS['dbpass'],
        [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]
    );
} catch (Throwable $e) {
    fail_api(500, 'Khong ket noi duoc MySQL. Hay kiem tra XAMPP va database uongbigo.');
}

$method = strtoupper($_SERVER['REQUEST_METHOD']);
$path = $_GET['path'] ?? '/';
$path = '/' . trim($path, '/');
$segs = array_values(array_filter(explode('/', trim($path, '/')), 'strlen'));
$body = json_input();

function current_user() {
    return $_SESSION['ubg_user'] ?? null;
}
function require_login() {
    $u = current_user();
    if (!$u) fail_api(401, 'Phien dang nhap da het han, vui long dang nhap lai.');
    return $u;
}
function now_iso() {
    return date('c');
}
function user_view($u) {
    return ['maNguoiDung'=>(int)$u['maNguoiDung'], 'hoTen'=>$u['hoTen'], 'email'=>$u['email'], 'vaiTro'=>$u['vaiTro']];
}
function menu_view($r) {
    return [
        'maMon'=>(int)$r['maMon'], 'tenMon'=>$r['tenMon'], 'donGia'=>(float)$r['donGia'],
        'moTa'=>$r['moTa'] ?? '', 'hinhAnh'=>$r['hinhAnh'] ?? '', 'trangThai'=>$r['trangThai']
    ];
}
function order_view($pdo, $o) {
    $st=$pdo->prepare("SELECT maMon, tenMon, soLuong, donGia FROM order_items WHERE maDonHang=? ORDER BY id");
    $st->execute([$o['maDonHang']]);
    $items=[];
    foreach($st as $it) $items[]=[
        'maMon'=>(int)$it['maMon'], 'tenMon'=>$it['tenMon'],
        'soLuong'=>(int)$it['soLuong'], 'donGia'=>(float)$it['donGia']
    ];
    return [
        'maDonHang'=>(int)$o['maDonHang'],
        'maDonRutGon'=>$o['maDonRutGon'],
        'ngayDat'=>$o['ngayDat'],
        'thoiGianNhan'=>$o['thoiGianNhan'],
        'trangThai'=>$o['trangThai'],
        'tongTien'=>(float)$o['tongTien'],
        'chiTietDonHangs'=>$items
    ];
}

try {
    /* AUTH */
    if ($method==='POST' && $path==='/auth/login') {
        $email=strtolower(trim((string)($body['email']??'')));
        $pw=(string)($body['matKhau']??'');
        $st=$pdo->prepare("SELECT * FROM users WHERE LOWER(email)=? LIMIT 1");
        $st->execute([$email]); $u=$st->fetch();
        if (!$u || !hash_equals($u['matKhau'], hash('sha256',$pw))) fail_api(401,'Sai email hoac mat khau.');
        $_SESSION['ubg_user']=user_view($u);
        $redirect=['ROLE_BUYER'=>'menu.html','ROLE_STAFF'=>'kds.html','ROLE_ADMIN'=>'admin/dashboard.html'][$u['vaiTro']]??'index.html';
        out(['accessToken'=>session_id(),'nguoiDung'=>user_view($u),'redirectTo'=>$redirect]);
    }

    if ($method==='POST' && $path==='/auth/register') {
        $email=strtolower(trim((string)($body['email']??'')));
        $hoTen=trim((string)($body['hoTen']??''));
        $pw=(string)($body['matKhau']??'');
        $role=in_array($body['vaiTro']??'', ['ROLE_BUYER','ROLE_STAFF'], true) ? $body['vaiTro'] : 'ROLE_BUYER';
        if(!$hoTen || !$email || strlen($pw)<6) fail_api(400,'Vui long nhap day du thong tin hop le.');
        $st=$pdo->prepare("SELECT COUNT(*) FROM users WHERE LOWER(email)=?");
        $st->execute([$email]);
        if($st->fetchColumn()>0) fail_api(409,'Email nay da duoc su dung.');
        $st=$pdo->prepare("INSERT INTO users(hoTen,email,matKhau,vaiTro) VALUES(?,?,?,?)");
        $st->execute([$hoTen,$email,hash('sha256',$pw),$role]);
        out(['message'=>'Dang ky thanh cong.']);
    }

    if ($method==='POST' && $path==='/auth/forgot-password') {
        $email=strtolower(trim((string)($body['email']??'')));
        $st=$pdo->prepare("SELECT maNguoiDung FROM users WHERE LOWER(email)=? LIMIT 1");
        $st->execute([$email]); $u=$st->fetch();
        if(!$u) fail_api(404,'Khong tim thay tai khoan voi email nay.');
        $tmp='Tam@'.random_int(1000,9999);
        $st=$pdo->prepare("UPDATE users SET matKhau=? WHERE maNguoiDung=?");
        $st->execute([hash('sha256',$tmp),$u['maNguoiDung']]);
        out(['message'=>'Da tao mat khau tam thoi, ban co the dang nhap ngay bang mat khau nay.','matKhauTamThoi'=>$tmp]);
    }

    /* MENU */
    if ($method==='GET' && $path==='/menu') {
        $rows=$pdo->query("SELECT * FROM menu_items WHERE trangThai <> 'DA_XOA' ORDER BY maMon")->fetchAll();
        out(array_map('menu_view',$rows));
    }

    /* ORDERS - BUYER */
    if ($method==='GET' && $path==='/donhang/cua-toi') {
        $u=require_login();
        $st=$pdo->prepare("SELECT * FROM orders WHERE maNguoiDung=? ORDER BY ngayDat DESC");
        $st->execute([$u['maNguoiDung']]);
        $res=[]; foreach($st as $o) $res[]=order_view($pdo,$o);
        out($res);
    }

    if ($method==='POST' && $path==='/donhang') {
        $u=require_login();
        $items=is_array($body['items']??null)?$body['items']:[];
        if(!$items) fail_api(400,'Gio hang dang trong.');
        $pdo->beginTransaction();
        $details=[]; $total=0;
        foreach($items as $it) {
            $id=(int)($it['maMon']??0); $qty=max(1,(int)($it['soLuong']??1));
            $st=$pdo->prepare("SELECT * FROM menu_items WHERE maMon=? AND trangThai='CON_HANG' LIMIT 1");
            $st->execute([$id]); $m=$st->fetch();
            if(!$m) continue;
            $details[]=['maMon'=>(int)$m['maMon'],'tenMon'=>$m['tenMon'],'soLuong'=>$qty,'donGia'=>(float)$m['donGia']];
            $total += (float)$m['donGia']*$qty;
        }
        if(!$details){$pdo->rollBack(); fail_api(400,'Cac mon trong gio hang hien khong con hang.');}
        $short='DH'.str_pad((string)(time()%1000000),4,'0',STR_PAD_LEFT);
        $st=$pdo->prepare("INSERT INTO orders(maNguoiDung,maDonRutGon,ngayDat,thoiGianNhan,trangThai,tongTien) VALUES(?,?,?,?,?,?)");
        $st->execute([$u['maNguoiDung'],$short,now_iso(),$body['thoiGianNhan']??now_iso(),'CHO_THANH_TOAN',$total]);
        $oid=(int)$pdo->lastInsertId();
        $st=$pdo->prepare("UPDATE orders SET maDonRutGon=? WHERE maDonHang=?");
        $st->execute(['DH'.str_pad($oid,4,'0',STR_PAD_LEFT),$oid]);
        $st=$pdo->prepare("INSERT INTO order_items(maDonHang,maMon,tenMon,soLuong,donGia) VALUES(?,?,?,?,?)");
        foreach($details as $d) $st->execute([$oid,$d['maMon'],$d['tenMon'],$d['soLuong'],$d['donGia']]);
        $pdo->commit();
        $st=$pdo->prepare("SELECT * FROM orders WHERE maDonHang=?"); $st->execute([$oid]);
        out(order_view($pdo,$st->fetch()));
    }

    /* PAYMENT */
    if ($method==='POST' && preg_match('#^/thanhtoan/(\d+)/qr$#',$path,$m)) {
        require_login(); $oid=(int)$m[1];
        $st=$pdo->prepare("SELECT * FROM orders WHERE maDonHang=?"); $st->execute([$oid]); $o=$st->fetch();
        if(!$o) fail_api(404,'Khong tim thay don hang.');
        if($o['trangThai']!=='CHO_THANH_TOAN') fail_api(400,'Don hang nay khong o trang thai cho thanh toan.');
        $qr='QR'.$oid.'-'.time(); $exp=date('c',time()+300);
        $st=$pdo->prepare("UPDATE orders SET maQr=?,thoiHanThanhToan=? WHERE maDonHang=?");
        $st->execute([$qr,$exp,$oid]);
        out(['maDonHang'=>$oid,'maQr'=>$qr,'phuongThuc'=>$_GET['phuongThuc']??'VIETQR','soTien'=>(float)$o['tongTien'],'qrImageBase64'=>null,'thoiHanThanhToan'=>$exp]);
    }

    if ($method==='POST' && $path==='/thanhtoan/webhook') {
        require_login();
        $st=$pdo->prepare("SELECT * FROM orders WHERE maDonHang=? AND maQr=? LIMIT 1");
        $st->execute([(int)($body['maDonHang']??0),(string)($body['maQr']??'')]); $o=$st->fetch();
        if(!$o) fail_api(404,'Khong tim thay giao dich thanh toan.');
        $status=($body['ketQua']??'')==='THANH_CONG'?'DA_THANH_TOAN':'CHO_THANH_TOAN';
        $st=$pdo->prepare("UPDATE orders SET trangThai=? WHERE maDonHang=?"); $st->execute([$status,$o['maDonHang']]);
        $st=$pdo->prepare("SELECT * FROM orders WHERE maDonHang=?"); $st->execute([$o['maDonHang']]);
        out(order_view($pdo,$st->fetch()));
    }

    /* KDS */
    if ($method==='GET' && $path==='/kds/orders') {
        require_login();
        $rows=$pdo->query("SELECT * FROM orders WHERE trangThai IN ('DA_THANH_TOAN','DANG_CHUAN_BI','SAN_SANG_NHAN') ORDER BY ngayDat ASC")->fetchAll();
        $res=[]; foreach($rows as $o)$res[]=order_view($pdo,$o); out($res);
    }
    if ($method==='PATCH' && preg_match('#^/kds/orders/(\d+)/status$#',$path,$m)) {
        require_login(); $oid=(int)$m[1];
        $st=$pdo->prepare("SELECT * FROM orders WHERE maDonHang=?");$st->execute([$oid]);$o=$st->fetch();
        if(!$o)fail_api(404,'Khong tim thay don hang.');
        $st=$pdo->prepare("UPDATE orders SET trangThai=? WHERE maDonHang=?");$st->execute([(string)($body['trangThaiMoi']??''),$oid]);
        $st=$pdo->prepare("SELECT * FROM orders WHERE maDonHang=?");$st->execute([$oid]);out(order_view($pdo,$st->fetch()));
    }
    if ($method==='PATCH' && preg_match('#^/kds/mon/(\d+)/toggle$#',$path,$m)) {
        require_login(); $id=(int)$m[1];
        $st=$pdo->prepare("SELECT * FROM menu_items WHERE maMon=?");$st->execute([$id]);$mon=$st->fetch();
        if(!$mon)fail_api(404,'Khong tim thay mon an.');
        $new=$mon['trangThai']==='CON_HANG'?'HET_HANG':'CON_HANG';
        $st=$pdo->prepare("UPDATE menu_items SET trangThai=? WHERE maMon=?");$st->execute([$new,$id]);
        $st=$pdo->prepare("SELECT * FROM menu_items WHERE maMon=?");$st->execute([$id]);out(menu_view($st->fetch()));
    }

    /* ADMIN */
    if ($method==='GET' && $path==='/admin/thong-ke') {
        require_login();
        $paid="'DA_THANH_TOAN','DANG_CHUAN_BI','SAN_SANG_NHAN','HOAN_THANH'";
        $tong=$pdo->query("SELECT COALESCE(SUM(tongTien),0) FROM orders WHERE trangThai IN ($paid)")->fetchColumn();
        $done=$pdo->query("SELECT COUNT(*) FROM orders WHERE trangThai='HOAN_THANH'")->fetchColumn();
        $all=$pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
        $selling=$pdo->query("SELECT COUNT(*) FROM menu_items WHERE trangThai='CON_HANG'")->fetchColumn();
        $outstock=$pdo->query("SELECT COUNT(*) FROM menu_items WHERE trangThai='HET_HANG'")->fetchColumn();
        out(['tongDoanhThu'=>(float)$tong,'tongSoDonHoanThanh'=>(int)$done,'tongSoDonTatCa'=>(int)$all,'soMonDangBan'=>(int)$selling,'soMonHetHang'=>(int)$outstock]);
    }
    if ($method==='GET' && $path==='/admin/don-hang') {
        require_login(); $rows=$pdo->query("SELECT * FROM orders ORDER BY ngayDat DESC")->fetchAll();
        $res=[];foreach($rows as $o)$res[]=order_view($pdo,$o);out($res);
    }
    if ($method==='GET' && $path==='/admin/mon-an') {
        require_login();$rows=$pdo->query("SELECT * FROM menu_items ORDER BY maMon")->fetchAll();out(array_map('menu_view',$rows));
    }
    if ($method==='POST' && $path==='/admin/mon-an') {
        require_login();
        $ten=trim((string)($body['tenMon']??''));$gia=(float)($body['donGia']??0);
        if(!$ten||$gia<=0)fail_api(400,'Ten mon va don gia khong hop le.');
        $st=$pdo->prepare("INSERT INTO menu_items(tenMon,donGia,moTa,hinhAnh,trangThai) VALUES(?,?,?,?,?)");
        $st->execute([$ten,$gia,$body['moTa']??'',$body['hinhAnh']??'','CON_HANG']);
        $id=(int)$pdo->lastInsertId();$st=$pdo->prepare("SELECT * FROM menu_items WHERE maMon=?");$st->execute([$id]);out(menu_view($st->fetch()));
    }
    if ($method==='PUT' && preg_match('#^/admin/mon-an/(\d+)$#',$path,$m)) {
        require_login();$id=(int)$m[1];
        $ten=trim((string)($body['tenMon']??''));$gia=(float)($body['donGia']??0);
        if(!$ten||$gia<=0)fail_api(400,'Ten mon va don gia khong hop le.');
        $st=$pdo->prepare("UPDATE menu_items SET tenMon=?,donGia=?,moTa=?,hinhAnh=?,trangThai=COALESCE(?,trangThai) WHERE maMon=?");
        $st->execute([$ten,$gia,$body['moTa']??'',$body['hinhAnh']??'',($body['trangThai']??null),$id]);
        $st=$pdo->prepare("SELECT * FROM menu_items WHERE maMon=?");$st->execute([$id]);$mon=$st->fetch();if(!$mon)fail_api(404,'Khong tim thay mon an.');out(menu_view($mon));
    }
    if ($method==='DELETE' && preg_match('#^/admin/mon-an/(\d+)$#',$path,$m)) {
        require_login();$id=(int)$m[1];$st=$pdo->prepare("UPDATE menu_items SET trangThai='DA_XOA' WHERE maMon=?");$st->execute([$id]);out(['message'=>'Da xoa mon an.']);
    }

    fail_api(404,'Khong tim thay endpoint.');
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    fail_api(500,'Loi may chu: '.$e->getMessage());
}
?>