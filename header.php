<header>
    <div class="circle-logo">
        <img src="" alt="" />
    </div>
    <div class="logo">
        <h1>THE PREMIERE CLUB</h1>
    </div>

    <nav>
        <ul>
            <li><a href="mainpage.php">Home<?phpecho $_SESSION['email']?></a></li>
            <li><a href="allMovies.php">Movies</a></li>
            <li><a href="viewCinemas.php">Cinemas</a></li>
            <?php
                if(isset($_SESSION['loggedin']) && $_SESSION['loggedin']) {
                    echo "<li><a href='viewaccount.php'>Account</a></li>";
                    echo "<li><a href='logout.php'>Log Out</a></li>";
                }
                else {
                    echo "<li><a href='loginpage.php'>Log In</a></li>";
                    echo "<li><a href='accountsignup.php'>Sign Up</a></li>";
                }
            ?>
        </ul>
    </nav>
</header>