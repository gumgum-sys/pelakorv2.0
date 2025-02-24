<?php
session_start();
include 'connect-db.php';
include 'functions/functions.php';

// Validasi login
cekAdmin();

$jumlahDataPerHalaman = 6; // 3 card per row, 2 rows
$query = mysqli_query($connect,"SELECT * FROM pelanggan");
$jumlahData = mysqli_num_rows($query);
$jumlahHalaman = ceil($jumlahData / $jumlahDataPerHalaman);

if (isset($_GET["page"])){
    $halamanAktif = $_GET["page"];
} else {
    $halamanAktif = 1;
}

$awalData = ($jumlahDataPerHalaman * $halamanAktif) - $jumlahDataPerHalaman;
$pelanggan = mysqli_query($connect,"SELECT * FROM pelanggan ORDER BY id_pelanggan DESC LIMIT $awalData, $jumlahDataPerHalaman");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include "headtags.html"; ?>
    <title>List Pelanggan</title>
    <style>
        .card-image { 
            height: 250px;
            overflow: hidden;
            cursor: pointer;
        }
        .card-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .card-action {
            display: flex;
            justify-content: space-between;
            padding: 10px;
        }
        .modal {
            max-height: 80% !important;
        }
        .card-contact {
            margin: 10px 0;
            color: #666;
        }
    </style>
</head>
<body>

    <?php include 'header.php'; ?>

    <div class="container">
        <h3 class="header light center">List Pelanggan</h3>
        
        <!-- Searching -->
        <div class="row">
            <div class="col s12 center">
                <div class="input-field inline">
                    <!-- Name "keyword" agar kita bisa tangkap di JS -->
                    <input type="text" name="keyword" placeholder="Cari pelanggan...">
                    <i class="material-icons prefix">search</i>
                </div>
            </div>
        </div>

        <!-- Card Container -->
        <div class="row card-container">
            <?php foreach ($pelanggan as $dataPelanggan) : ?>
            <div class="col s12 m6 l4">
                <div class="card">
                    <div class="card-image" onclick="showDetails(<?= htmlspecialchars(json_encode($dataPelanggan)) ?>)">
                        <img src="img/pelanggan/<?= !empty($dataPelanggan['foto']) ? $dataPelanggan['foto'] : 'default.jpg' ?>" 
                             alt="<?= $dataPelanggan["nama"] ?>">
                    </div>
                    <div class="card-content">
                        <span class="card-title truncate"><?= $dataPelanggan["nama"] ?></span>
                        <div class="card-contact">
                            <i class="material-icons tiny">phone</i> <?= $dataPelanggan["telp"] ?>
                        </div>
                        <div class="card-action">
                            <a class="btn blue darken-2" href="ganti-kata-sandi.php?id=<?= $dataPelanggan['id_pelanggan'] ?>&type=pelanggan">
                                <i class="material-icons">lock_reset</i>
                            </a>
                            <a class="btn red darken-2" href="list-pelanggan.php?hapus=<?= $dataPelanggan['id_pelanggan'] ?>" 
                               onclick="return confirm('Apakah anda yakin ingin menghapus data ?')">
                                <i class="material-icons">delete</i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach ?>
        </div>

        <!-- Pagination (server-side default) -->
        <div class="row center">
            <ul class="pagination pagination-default">
                <?php if($halamanAktif > 1) : ?>
                    <li class="waves-effect">
                        <a href="?page=<?= $halamanAktif - 1; ?>">
                            <i class="material-icons">chevron_left</i>
                        </a>
                    </li>
                <?php endif; ?>
                
                <?php for($i = 1; $i <= $jumlahHalaman; $i++) : ?>
                    <?php if($i == $halamanAktif) : ?>
                        <li class="active blue darken-2"><a href="?page=<?= $i; ?>"><?= $i ?></a></li>
                    <?php else : ?>
                        <li class="waves-effect"><a href="?page=<?= $i; ?>"><?= $i ?></a></li>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if($halamanAktif < $jumlahHalaman) : ?>
                    <li class="waves-effect">
                        <a href="?page=<?= $halamanAktif + 1; ?>">
                            <i class="material-icons">chevron_right</i>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>

    <!-- Modal Detail -->
    <div id="detailModal" class="modal">
        <div class="modal-content">
            <h4>Detail Pelanggan</h4>
            <div class="row">
                <div class="col s12">
                    <ul class="collection">
                        <li class="collection-item">ID Pelanggan: <span id="modal-id-pelanggan"></span></li>
                        <li class="collection-item">Email: <span id="modal-email"></span></li>
                        <li class="collection-item">Kota: <span id="modal-kota"></span></li>
                        <li class="collection-item">Alamat: <span id="modal-alamat"></span></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <a href="#!" class="modal-close waves-effect waves-green btn-flat">Tutup</a>
        </div>
    </div>

    <?php include "footer.php"; ?>

    <!-- Script JavaScript untuk pencarian AJAX + pagination -->
    <script>
document.addEventListener('DOMContentLoaded', function() {
    var elems = document.querySelectorAll('.modal');
    var instances = M.Modal.init(elems);

    const searchInput = document.querySelector('input[name="keyword"]');
    const cardContainer = document.querySelector('.card-container');
    const paginationDefault = document.querySelector('.pagination-default'); 
    let currentPage = 1;
    let timeoutId = null;

    // Hilangkan pagination default saat AJAX dipakai
    paginationDefault.style.display = 'none';

    // Event listener pencarian (debounce 300ms)
    searchInput.addEventListener('input', function() {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => {
            currentPage = 1;
            searchData(this.value, currentPage);
        }, 300);
    });

    function searchData(keyword, page) {
        fetch(`ajax/cari.php?type=pelanggan&keyword=${encodeURIComponent(keyword)}&page=${page}`)
            .then(response => response.json())
            .then(json => {
                if(json.error) {
                    console.error(json.error);
                    return;
                }
                updateList(json.data);
                updatePagination(json.totalPages, json.currentPage, keyword);
            })
            .catch(error => console.error('Error:', error));
    }

    function updateList(items) {
        cardContainer.innerHTML = '';
        
        if(!items || items.length === 0) {
            cardContainer.innerHTML = '<div class="col s12 center"><h5>Tidak ada hasil yang ditemukan</h5></div>';
            return;
        }

        items.forEach(item => {
            const card = `
                <div class="col s12 m6 l4">
                    <div class="card">
                        <div class="card-image" onclick="showDetails(${JSON.stringify(item)})">
                            <img src="img/pelanggan/${item.foto || 'default.jpg'}" alt="${item.nama}">
                        </div>
                        <div class="card-content">
                            <span class="card-title truncate">${item.nama}</span>
                            <div class="card-contact">
                                <i class="material-icons tiny">phone</i> ${item.telp}
                            </div>
                            <div class="card-action">
                                <a class="btn blue darken-2" href="ganti-kata-sandi.php?id=${item.id_pelanggan}&type=pelanggan">
                                    <i class="material-icons">lock_reset</i>
                                </a>
                                <a class="btn red darken-2" href="list-pelanggan.php?hapus=${item.id_pelanggan}" 
                                   onclick="return confirm('Apakah anda yakin ingin menghapus data ?')">
                                    <i class="material-icons">delete</i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            cardContainer.insertAdjacentHTML('beforeend', card);
        });
    }

    function updatePagination(totalPages, currentPage, keyword) {
        // Buat pagination baru di bawah container
        paginationDefault.innerHTML = '';
        paginationDefault.style.display = 'block';

        let paginationHTML = '';

        // Tombol previous
        if(currentPage > 1) {
            paginationHTML += `
                <li class="waves-effect">
                    <a href="#!" data-page="${currentPage - 1}">
                        <i class="material-icons">chevron_left</i>
                    </a>
                </li>
            `;
        }

        // Nomor halaman
        for(let i = 1; i <= totalPages; i++) {
            paginationHTML += `
                <li class="waves-effect ${i === currentPage ? 'active blue darken-2' : ''}">
                    <a href="#!" data-page="${i}">${i}</a>
                </li>
            `;
        }

        // Tombol next
        if(currentPage < totalPages) {
            paginationHTML += `
                <li class="waves-effect">
                    <a href="#!" data-page="${currentPage + 1}">
                        <i class="material-icons">chevron_right</i>
                    </a>
                </li>
            `;
        }

        paginationDefault.innerHTML = paginationHTML;

        // Event click pada pagination
        document.querySelectorAll('.pagination-default a').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const page = parseInt(this.dataset.page);
                searchData(keyword, page);
            });
        });
    }
});

function showDetails(data) {
    document.getElementById('modal-id-pelanggan').textContent = data.id_pelanggan;
    document.getElementById('modal-email').textContent = data.email;
    document.getElementById('modal-kota').textContent = data.kota;
    document.getElementById('modal-alamat').textContent = data.alamat;
    
    var modal = M.Modal.getInstance(document.getElementById('detailModal'));
    modal.open();
}
    </script>
</body>
</html>

<?php
// Proses hapus data pelanggan
if (isset($_GET["hapus"])){
    $idPelanggan = $_GET["hapus"];
    $query = mysqli_query($connect, "DELETE FROM pelanggan WHERE id_pelanggan = '$idPelanggan'");
    
    if (mysqli_affected_rows($connect) > 0){
        echo "
            <script>
                Swal.fire('Data Pelanggan Berhasil Di Hapus','','success').then(function(){
                    window.location = 'list-pelanggan.php';
                });
            </script>
        ";
    }
}
?>
