<?php
$conn = mysqli_connect("localhost", "root", "", "simbs");


// fungsi untuk menampilkan data dari database
function query($query){
    global $conn;


    $result = mysqli_query($conn, $query);
    $rows = [];
    while( $row = mysqli_fetch_assoc($result) ) {
        $rows[] = $row;
    }
    return $rows;
}


// fungsi untuk menambahkan data ke database
function tambah_data($data){
    global $conn;

    $judul = $data['judul'];
    $penulis = $data['penulis'];
    $penerbit = $data['penerbit'];
    $tahun= $data['tahun_terbit'];
    // $gambar = $data['gambar'];
    // upload gambar
    $cover = upload_gambar($judul);  
    if( !$cover ) {
        return false;
    }
    $isbn= $data['isbn'];
    $kategori= $data['id_kategori'];

    $query = "INSERT INTO buku (judul, penulis, penerbit, tahun_terbit, cover, isbn, id_kategori)
                  VALUES ('$judul', '$penulis', '$penerbit', '$tahun', '$cover', '$isbn', '$kategori')";
    mysqli_query($conn, $query);


    return mysqli_affected_rows($conn);    
}

// fungsi untuk upload gambar
function upload_gambar($judul) {


    // setting gambar
    $namaFile = $_FILES['cover']['name'];
    $ukuranFile = $_FILES['cover']['size'];
    $error = $_FILES['cover']['error'];
    $tmpName = $_FILES['cover']['tmp_name'];


    // cek apakah tidak ada gambar yang diupload
    if( $error === 4 ) {
        echo "<script>
                alert('pilih gambar terlebih dahulu!');
              </script>";
        return false;
    }


    // cek apakah yang diupload adalah gambar
    $ekstensiGambarValid = ['jpg', 'jpeg', 'png'];
    $ekstensiGambar = explode('.', $namaFile);
    $ekstensiGambar = strtolower(end($ekstensiGambar));
    if( !in_array($ekstensiGambar, $ekstensiGambarValid) ) {
        echo "<script>
                alert('yang anda upload bukan gambar!');
              </script>";
        return false;
    }


    // cek jika ukurannya terlalu besar
    // maks --> 5MB
    if( $ukuranFile > 5000000 ) {
        echo "<script>
                alert('ukuran gambar terlalu besar!');
              </script>";
        return false;
    }


    // lolos pengecekan, gambar siap diupload
    // generate nama gambar baru
    $namaFileBaru = $id . "_" . $judul;
    $namaFileBaru .= '.';
    $namaFileBaru .= $ekstensiGambar;


    move_uploaded_file($tmpName, 'img/' . $namaFileBaru);


    return $namaFileBaru;
}



// fungsi untuk menghapus data dari database
function hapus_data($id){
    global $conn;


    $query = "DELETE FROM buku WHERE id_buku = $id";


    $result = mysqli_query($conn, $query);


    return mysqli_affected_rows($conn);    
}




// fungsi untuk mengubah data dari database
function ubah_data($data){
    global $conn;


    $id = $data['id_buku'];
    $judul = $data['judul'];
    $penulis = $data['penulis'];
    $penerbit = $data['penerbit'];
    $tahun= $data['tahun_terbit'];
    $cover = $data['cover'];
    $isbn= $data['isbn'];
    $kategori= $data['id_kategori'];


    $query = "UPDATE buku SET
                judul = '$judul',
                penulis = '$penulis',
                penerbit = '$penerbit',
                tahun_terbit = '$tahun',
                cover = '$cover',
                isbn = '$isbn',
                id_kategori= '$kategori'
              WHERE id_buku = $id
             ";


     $result = mysqli_query($conn, $query);
     
     return mysqli_affected_rows($conn);
}

// fungsi untuk mencari data
function search_data($keyword){
    global $conn;


    $query = "SELECT 
    buku.*, 
    kategori.nama_kategori 
    FROM buku
    INNER JOIN kategori
    ON buku.id_kategori = kategori.id_kategori
    WHERE buku.judul LIKE '%$keyword%' OR buku.penulis LIKE '%$keyword%'
            ";
    return query($query);
}

function categories() {
    global $conn;
    
    $query = "SELECT id_kategori, nama_kategori FROM kategori ORDER BY nama_kategori DESC";
    
    return query($query); 
}


function register($data){
    global $conn;


    $username = strtolower($data['username']);
    $email = $data['email'];
    $password = mysqli_real_escape_string($conn, $data['password']);
    $confirm = strlen($password);


    // query untuk ngecek username yang diinputkan oleh user di database
    $query = mysqli_query($conn, "SELECT username FROM user WHERE username = '$username'");
    $result = mysqli_fetch_assoc($query);


    if($result != NULL){
        return "Username sudah terdaftar!";
    }


    if($confirm < 8){
        return "Pasword kurang dari 8 karakter!";
    }


    // enkripsi password
    $password = password_hash($password, PASSWORD_DEFAULT);


    // tambahkan userbaru ke database
    mysqli_query($conn, "INSERT INTO user (username, email, password) VALUES('$username', '$email', '$password')");


    return true;
}

// fungsi untuk login
function login($data){
    global $conn;


    $username = $data['username'];
    $password = $data['password'];


    $query = "SELECT * FROM user WHERE username = '$username'";
    $result = mysqli_query($conn, $query);


    if(mysqli_num_rows($result) === 1){
        $row = mysqli_fetch_assoc($result);


        if(password_verify($password, $row['password'])){
           $_SESSION ['login'] = true;
           $_SESSION ['username'] = $row['username'];
            return true;
        } else {
           
            return "Password salah!";
        }


    }else{
        return "Username tidak terdaftar!";
    }
}

