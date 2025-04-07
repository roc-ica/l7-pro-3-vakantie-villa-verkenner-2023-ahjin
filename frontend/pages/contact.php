<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="../styles/contact.css" />
    <link rel="stylesheet" href="../includes/header.css" />
    <link rel="stylesheet" href="../includes/footer.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet" />
    <link rel="icon" href="../img/logo.png" type="image/png" />
    <title>Vakantie Villas - IJsland</title>

</head>

<body>
    <?php include '../includes/header.php';
    ?>
    <header>
        <div class="opacity-div">
            <h1>CONTACT</h1>
            <p>Ervaar het noorderlicht, ruige landschappen en unieke avonturen!</p>
        </div>
    </header>
    <section class="content">
        <div class="info-content">
            <div class="width">
                <h2>Meer informatie</h2>
                <p class="inleiding">Wilt u meer informatie of bent u benieuwd wat wij voor u kunnen betekenen? Neem vrijblijvend <br> contact met ons op. </p>
            </div>
            <hr>
            <div class="align"><img src="../../assets/img/locatie.png" alt="">
                <p>Locatie</p>
            </div>
            <p>12 Þingholtsstræti, <br>3Reykjavik, Reykjavíkurborg</p>
            <hr>
            <div class="align"><img src="../../assets/img/telefoon.png" alt="">
                <p>Telefoon</p>
            </div>
            <p>T: <span>033 – 123 4567</span></p>
            <hr>
            <div class="align"><img src="../../assets/img/email.png" alt="">
                <p>E-mail</p>
            </div>
            <a href="mailto:support@ilvastgoed.com">support@ilvastgoed.com</a>
            <a href="mailto:contact@ilvastgoed.com">contact@ilvastgoed.com</a>
            <hr>
            <!-- New form starts here -->
            <div class="form-container">
                <form action="https://api.web3forms.com/submit" method="POST">
                    <input type="hidden" name="access_key" value="434f1bea-bfd1-45c2-966f-039118f088d4">
                    <div class="floating-label-container">
                        <input type="text" id="name" name="name" placeholder=" " required style="width: 100%;">
                        <label for="name">Naam</label>
                    </div>
                    <div class="floating-label-container">
                        <input type="email" id="email" name="email" placeholder=" " required style="width: 100%;">
                        <label for="email">E-mailadres</label>
                    </div>
                    <div class="floating-label-container">
                        <textarea id="message" name="message" rows="4" placeholder=" " required style="width: 100%;"></textarea>
                        <label for="message">Bericht</label>
                    </div>
                    <div class="submit-image">
                        <button type="submit">Versturen</button>
                    </div>
                </form>
            </div>
            <!-- New form ends here -->
        </div>
        </div>
        <div class="img-content">
            <img src="../../assets/img/reykjavik.png" alt="">
        </div>
    </section>

<?php include '../includes/footer.php'; ?>

  

</body>
<script src="../js/restaurant-slider.js"></script>
<script src="../js/sidebar.js"></script>

</html>