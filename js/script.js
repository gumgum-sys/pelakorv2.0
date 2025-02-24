$(document).ready(function () {
    $('.sidenav').sidenav();
});

document.addEventListener('DOMContentLoaded', function () {
    var elems = document.querySelectorAll('.slider');
    // Definisikan options untuk slider
    var options = {
        indicators: true,         // Tampilkan indikator
        height: 400,             // Tinggi slider
        interval: 6000,          // Interval transisi (dalam ms)
        duration: 500,           // Durasi transisi (dalam ms)
        autoplay: true           // Auto play slider
    };
    var instances = M.Slider.init(elems, options);
});