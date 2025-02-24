<?php
session_start();
include 'connect-db.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Team LaundryKu</title>
  <?php include "headtags.html"; ?>
  <style>
    body {
      background: #f5f5f5;
      font-family: 'Roboto', sans-serif;
    }
    .team-container {
      padding: 40px 0;
    }
    .team-title {
      text-align: center;
      margin-bottom: 40px;
      font-size: 2.5rem;
      font-weight: 600;
      color: #333;
    }
    .card.team-card {
      overflow: hidden;
      border-radius: 10px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
      transition: transform 0.3s ease;
      background: #fff;
    }
    .card.team-card:hover {
      transform: translateY(-5px);
    }
    .card-image img {
      width: 100%;
      height: 250px;
      object-fit: cover;
    }
    .card-content {
      padding: 20px;
    }
    .card-title {
      font-size: 1.5rem;
      font-weight: 600;
      color: #00796b;
      margin-bottom: 10px;
    }
    .card-content p {
      font-size: 1rem;
      color: #555;
      line-height: 1.6;
    }
    .card-action {
      background: #00796b;
      padding: 15px;
      text-align: center;
    }
    .card-action a {
      color: #fff;
      text-decoration: none;
      font-weight: 500;
    }
    @media only screen and (max-width: 600px) {
      .card-image img {
        height: 200px;
      }
    }
  </style>
</head>
<body>
  
  <?php include "header.php"; ?>
  
  <div class="container team-container">
    <h3 class="team-title">Team LaundryKu</h3>
    <div class="row">
      <!-- Profil Fadjar Aryo Seto -->
      <div class="col s12 m6">
        <div class="card team-card">
          <div class="card-image">
            <img src="img/fotoseto.jpg" alt="Fadjar Aryo Seto">
          </div>
          <div class="card-content">
            <span class="card-title">Fadjar Aryo Seto</span>
            <p>
              Pemimpin visioner dan inovator di balik LaundryKu. Dengan semangat pantang menyerah, Fadjar selalu mencari cara terbaik untuk mengantarkan layanan laundry yang terpercaya dan efisien ke seluruh pelanggan.
            </p>
          </div>
          <div class="card-action">
            <a href="#">Selengkapnya</a>
          </div>
        </div>
      </div>
      <!-- Profil Adam GPT -->
      <div class="col s12 m6">
        <div class="card team-card">
          <div class="card-image">
            <img src="img/fotoadam.jpg" alt="Adam GPT">
          </div>
          <div class="card-content">
            <span class="card-title">Adam GPT</span>
            <p>
              Sistem canggih dan asisten AI terintegrasi yang siap memberikan solusi cepat dan tepat. Adam GPT adalah jantung digital LaundryKu, menjamin dukungan inovatif di setiap langkah operasional.
            </p>
          </div>
          <div class="card-action">
            <a href="#">Selengkapnya</a>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <?php include "footer.php"; ?>
  
</body>
</html>
