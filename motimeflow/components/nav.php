<nav class="navbar navbar-expand-lg navbar-dark bg-MoBlue">
    <div class="container-fluid">
        <a class="navbar-brand ms-2" href="https://moplant.nl/motimeflow/index.php">
            <img src="/motimeflow/img/mo4u-icon.png" alt="" width="30" height="30" class="d-inline-block align-text-top">
        </a>
        <span class="d-none d-md-block me-auto fs-4">
            Welkom bij MoTimeflow <?= $_SESSION["user"]["bedrijfsnaam"] ?>
        </span>
        <span class="d-block d-md-none me-auto fs-4">
            MoTimeflow
        </span>
        <ul class="nav navbar-nav navbar-right">
            <li><a class="nav-link text-light" href="https://moplant.nl/motimeflow/inc/loguit.php"><i class="fa-solid fa-right-from-bracket me-1"></i>Uitloggen</a></li>
        </ul>
    </div>
</nav>