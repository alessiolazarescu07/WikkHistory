<?php
session_start();
require '../settings/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
$fullName = $_SESSION['nome'] . " " . $_SESSION['cognome'];
?>
<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monuments | WikkHistory</title>
    <link href="https://fonts.googleapis.com/css2?family=Titillium+Web:wght@300;400;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="icon" type="image/x-icon" href="../img/favicon.png">
    <style>
        :root {
            --gold: #aa8b56;
            --gold-bright: #d4af37;
            --glass: rgba(255, 255, 255, 0.08);
            --sidebar-width: 280px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Titillium Web', sans-serif;
            background: linear-gradient(rgba(0, 0, 0, 0.85), rgba(0, 0, 0, 0.85)), url('../img/background.jpg');
            background-size: cover;
            background-attachment: fixed;
            color: white;
            margin: 0;
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: var(--sidebar-width);
            background: rgba(0, 0, 0, 0.95);
            padding: 40px 20px;
            display: flex;
            flex-direction: column;
            border-right: 1px solid var(--gold);
            height: 100vh;
            position: sticky;
            top: 0;
            transition: 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1001;
        }

        .mobile-header {
            display: none;
            background: #000;
            padding: 15px 20px;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--gold);
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1002;
        }

        .sidebar .logo {
            align-items: center;
            margin-top: 40px;
            margin-bottom: 40px;
        }

        .nav-link {
            color: #ccc;
            text-decoration: none;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 12px;
            transition: 0.3s;
            display: flex;
            align-items: center;
            border: 1px solid transparent;
            cursor: pointer;
        }

        .nav-link:hover,
        .nav-link.active {
            background: var(--glass);
            border-color: var(--gold);
            color: white;
        }

        .nav-link.active {
            font-weight: bold;
        }

        .nav-link i {
            margin-right: 15px;
            color: var(--gold);
            width: 20px;
        }

        .main-content {
            flex: 1;
            padding: 40px;
            width: 100%;
        }

        .monuments-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 25px;
            margin-top: 30px;
        }

        .monument-card {
            background: var(--glass);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            overflow: hidden;
            backdrop-filter: blur(10px);
            transition: 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
            color: inherit;
            display: flex;
            flex-direction: column;
        }

        .monument-card:hover {
            transform: translateY(-10px);
            border-color: var(--gold);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        }

        .card-img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-bottom: 2px solid var(--gold);
        }

        .card-body {
            padding: 20px;
        }

        .card-body h3 {
            color: var(--gold);
            margin: 0 0 10px 0;
            font-size: 1.4rem;
        }

        .card-body p {
            color: #ccc;
            font-size: 0.95rem;
            line-height: 1.5;
            margin-bottom: 20px;
        }


        .btn-view {
            color: var(--gold-bright);
            font-weight: bold;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            display: flex;
            align-items: center;
        }

        .btn-view i {
            margin-left: 10px;
            transition: 0.3s;
        }

        .monument-card:hover .btn-view i {
            transform: translateX(5px);
        }

        @media screen and (min-width: 481px) and (max-width: 1024px) and (orientation: portrait) {
            .sidebar {
                position: fixed;
                left: -100%;
                width: 100%;
                height: 100vh;
                z-index: 2000;
                transition: 0.5s cubic-bezier(0.4, 0, 0.2, 1);
                padding-top: 80px;
                box-shadow: 10px 0 30px rgba(0, 0, 0, 0.8);
                border-right: none;
                border-top: 1px solid var(--gold);
                transition: 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            }

            .sidebar a {
                font-size: 1.3rem;
                padding: 20px;
            }

            .sidebar a i {
                font-size: 1.6rem;
            }

            .sidebar.active {
                left: 0;
            }

            .mobile-header {
                display: flex !important;
                z-index: 2100;
            }

            .close-menu {
                display: block !important;
                position: absolute;
                top: 20px;
                right: 20px;
            }

            .main-content {
                margin-left: 0 !important;
                padding-top: 100px;
            }

            .sidebar .logo {
                display: none;
            }

            .logo-mobile {
                width: 150px;
                height: auto;
            }
        }

        @media screen and (max-width: 932px) and (orientation: landscape) {
            body {
                flex-direction: column;
            }

            .mobile-header {
                display: flex;
                z-index: 2100;
            }

            .logo-mobile {
                width: 110px;
                height: auto;
            }

            .sidebar {
                position: fixed;
                left: -100%;
                width: 100%;
                top: 0;
                padding-top: 80px;
                z-index: 2000;
                box-shadow: 10px 0 30px rgba(0, 0, 0, 0.8);
                border-right: none;
                border-top: 1px solid var(--gold);
                transition: 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            }

            .sidebar.active {
                left: 0;
            }

            .main-content {
                padding: 75px 20px 20px 20px;
            }

            .main-content h1 {
                font-size: 1.5rem !important;
                margin-bottom: 20px !important;
            }

            .logo {
                display: none;
            }
        }

        @media screen and (max-width: 480px) {
            body {
                flex-direction: column;
            }

            .sidebar {
                position: fixed;
                left: -100%;
                width: 100%;
                height: 100vh;
                top: 0;
                padding-top: 80px;
                background-color: #000;
                border-right: none;
            }

            .sidebar.active {
                left: 0;
            }

            .mobile-header {
                display: flex;
            }

            .main-content {
                padding: 100px 20px 40px 20px;
            }

            .monuments-grid {
                grid-template-columns: 1fr;
            }

            .close-menu {
                position: absolute;
                top: 25px;
                right: 25px;
                font-size: 2rem;
                color: var(--gold);
                display: block;
            }

            .logo {
                display: none;
            }

            .logo-mobile {
                width: 150px;
                align-self: center
            }
        }
    </style>
</head>

<body>
    <div class="mobile-header">
        <img class="logo-mobile" src="../img/wordmark.png" alt="logo">
        <i class="fa fa-bars" style="font-size:1.5rem; color:var(--gold); cursor:pointer;"
            onclick="toggleSidebar()"></i>
    </div>

    <div class="sidebar" id="sidebar">
        <img src="../img/wordmark.png" class="logo" alt="Logo">
        <nav>
            <a href="../dashboard.php" class="nav-link"><i class="fa fa-home"></i> Home Page</a>
            <a href="monuments.php" class="nav-link active"><i class="fa fa-university"></i> Monuments</a>
            <a href="myNotes.php" class="nav-link"><i class="fa fa-book"></i>My Notes</a>
        </nav>
        <a href="../logout.php" style="margin-top:auto; color:#ff4d4d; text-decoration:none; padding:15px;"><i
                class="fa fa-sign-out-alt"></i> Logout</a>
    </div>

    <div class="main-content">
        <h1 style="color:var(--gold); border-left: 4px solid var(--gold); padding-left: 20px;">MONUMENTS</h1>
        <p style="opacity: 0.7;">Select a monument to look it up on the map and discover what others had to say</p>

        <div class="monuments-grid" id="monumentsGrid">
            <a href="../dashboard.php?monument=porta_palatina" class="monument-card">
                <img src="../img/monuments/porta_palatina.jpg" class="card-img" alt="Porta Palatina">
                <div class="card-body">
                    <h3>Porta Palatina</h3>
                    <p>The northern entrance to Augusta Taurinorum, featuring two 30-meter-high towers and a central
                        body with four arched passageways. Originally equipped with a fortified inner courtyard for tax
                        collection and defense, the gate survived the centuries by adapting its role. During the Middle
                        Ages, it was reinforced for military use, and in the 1700s, it was integrated into urban
                        buildings and repurposed as a prison, maintaining its architectural integrity until modern
                        restoration.</p>
                    <p><b>Restored in: </b>20th century</p>
                    <div class="btn-view">Open in the map <i class="fa fa-arrow-right"></i></div>
                </div>
            </a>

            <a href="../dashboard.php?monument=teatro_romano" class="monument-card">
                <img src="../img/monuments/teatro_romano.jpg" class="card-img" alt="Teatro Romano">
                <div class="card-body">
                    <h3>Teatro Romano</h3>
                    <p>Located in the northeastern corner of Augusta Taurinorum, the theater evolved from a 1st-century
                        BC wooden structure into a monument during the Flavian era. It featured a large semicircular
                        seating area and a sophisticated portico behind the stage. The theater remained active until the
                        4th century, when it was dismantled to provide materials for the city's first cathedral.</p>
                    <p><b>Discovered in: </b>1899</p>
                    <div class="btn-view">Open in the map <i class="fa fa-arrow-right"></i></div>
                </div>
            </a>

            <a href="../dashboard.php?monument=porta_decumana" class="monument-card">
                <img src="../img/monuments/porta_decumana.jpg" class="card-img" alt="Porta Palatina">
                <div class="card-body">
                    <h3>Porta Decumana (Palazzo Madama)</h3>
                    <p>The eastern entrance to the city, featuring a four-passage structure flanked by two sixteen-sided
                        towers. Built in the 1st century, it served as a vital gateway until the Middle Ages, when it
                        was gradually transformed into a fortified residence and later a castle. By the early 14th
                        century, the Roman passages were permanently closed as the structure evolved into the central
                        core of what is now Palazzo Madama, reflecting the city's transition from an imperial colony to
                        a medieval stronghold.</p>
                    <div class="btn-view">Open in the map <i class="fa fa-arrow-right"></i></div>
                </div>
            </a>

            <a href="../dashboard.php?monument=teatro_romano" class="monument-card">
                <img src="../img/monuments/foro_romano.jpg" class="card-img" alt="Teatro Romano">
                <div class="card-body">
                    <h3>Foro Romano</h3>
                    <p>Piazza Palazzo di Città, the historical "Piazza delle Erbe," has been Turin's civic and
                        commercial heart since Roman times, possibly serving as the ancient forum, a large square
                        surrounded by important public buildings, including temples and administrative buildings.</p>
                    <div class="btn-view">Open in the map <i class="fa fa-arrow-right"></i></div>
                </div>
            </a>

            <a href="../dashboard.php?monument=torre_consolata" class="monument-card">
                <img src="../img/monuments/torre_consolata.jpg" class="card-img" alt="Porta Palatina">
                <div class="card-body">
                    <h3>Torre Angolare</h3>
                    <p>The angular tower near the Consolata Sanctuary marked the northwest corner of Augusta Taurinorum.
                        It featured a square base and an octagonal upper structure, positioned astride the city walls
                        rather than projecting outward like the main gate towers. While it remained a vital part of the
                        city's defensive circuit throughout the Middle Ages, most of the tower and the surrounding
                        fortifications were dismantled during the major Baroque urban expansions.</p>
                    <p><b>Discovered in: </b>1884</p>
                    <div class="btn-view">Open in the map <i class="fa fa-arrow-right"></i></div>
                </div>
            </a>

            <a href="../dashboard.php?monument=domus_castello" class="monument-card">
                <img src="../img/monuments/domus_castello.jpg" class="card-img" alt="Domus Castello">
                <div class="card-body">
                    <h3>Domus Piazza Castello 51</h3>
                    <p>A suburban residence built in the mid-1st century, featuring a central courtyard and a
                        well-equipped kitchen. Significant renovations in the 2nd century introduced indoor heating and
                        redesigned floor plans. Following its abandonment in the 3rd century, the site was repurposed as
                        a small necropolis, where several simple inhumation graves from the 4th and 5th centuries were
                        established over the ruins of the former home.</p>
                    <p><b>Discovered in: </b>1995-1996</p>
                    <div class="btn-view">Open in the map <i class="fa fa-arrow-right"></i></div>
                </div>
            </a>

            <a href="../dashboard.php?monument=domus_san_carlo" class="monument-card">
                <img src="../img/monuments/domus_san_carlo.jpg" class="card-img" alt="Domus San Carlo">
                <div class="card-body">
                    <h3>Domus Piazza San Carlo</h3>
                    <p>A suburban house located just outside the southern city walls. Built in the late 1st century, it
                        included a storage room with wooden shelving. The site was abandoned in the 3rd century and left
                        behind a wealth of domestic artifacts, such as amphorae, kitchenware and oil lamps. It provides
                        a rare glimpse into suburban life in ancient Turin.</p>
                    <p><b>Discovered in: </b>2004-2005</p>
                    <div class="btn-view">Open in the map <i class="fa fa-arrow-right"></i></div>
                </div>
            </a>

            <a href="../dashboard.php?monument=domus_bonelli" class="monument-card">
                <img src="../img/monuments/domus_bonelli.jpg" class="card-img" alt="Domus Bonelli">
                <div class="card-body">
                    <h3>Domus Via Bonelli 11</h3>
                    <p>A 2nd-century noble residence spanning over 200 square meters. Its layout included eleven rooms
                        arranged around a central courtyard for rainwater collection. The house was renowned for its
                        luxurious floors, including a black-and-white geometric mosaic and a depiction of a winged Cupid
                        riding a dolphin.</p>
                    <p><b>Discovered in: </b>1993</p>
                    <div class="btn-view">Open in the map <i class="fa fa-arrow-right"></i></div>
                </div>
            </a>

            <a href="../dashboard.php?monument=domus_garibaldi" class="monument-card">
                <img src="../img/monuments/domus_garibaldi.jpg" class="card-img" alt="Domus Garibaldi">
                <div class="card-body">
                    <h3>Domus Via Garibaldi 18</h3>
                    <p>The residential complex consisted of 1st-century houses built along the city's main road.
                        Renovated in the 2nd century, the houses were embellished with geometric mosaics and equipped
                        with advanced underfloor heating systems. These houses reflect the high standard of urban living
                        in Augusta Taurinorum until their gradual decay and abandonment in the 4th century.</p>
                    <p><b>Discovered in: </b>1993-1995</p>
                    <div class="btn-view">Open in the map <i class="fa fa-arrow-right"></i></div>
                </div>
            </a>

            <a href="../dashboard.php?monument=domus_orfane" class="monument-card">
                <img src="../img/monuments/domus_orfane.jpg" class="card-img" alt="Domus Orfane">
                <div class="card-body">
                    <h3>Domus Via delle Orfane 20</h3>
                    <p>A 1st-3rd century complex featuring four large rooms around a colonnaded courtyard. It is famous
                        for its rare marble mosaics, most notably a well-preserved depiction of the myth of Actaeon
                        being transformed into a stag. The site also contains unique "basket-weave" geometric patterns,
                        marking it as a high-status building within ancient Augusta Taurinorum.</p>
                    <p><b>Discovered in: </b>2017</p>
                    <div class="btn-view">Open in the map <i class="fa fa-arrow-right"></i></div>
                </div>
            </a>

            <a href="../dashboard.php?monument=domus_bellezia" class="monument-card">
                <img src="../img/monuments/domus_bellezia.jpg" class="card-img" alt="Domus Bellezia">
                <div class="card-body">
                    <h3>Domus Via Bellezia 16</h3>
                    <p>A residence featuring a central reception room with an open courtyard. In the 2nd century, the
                        house was adorned with an elegant black-and-white mosaic floor depicting geometric stars and
                        floral motifs. After its decay, the site was used, during the Middle Age, as a burial ground, a
                        common transformation for ancient urban residential areas.</p>
                    <p><b>Discovered in: </b>2008</p>
                    <div class="btn-view">Open in the map <i class="fa fa-arrow-right"></i></div>
                </div>
            </a>

            <a href="../dashboard.php?monument=necropol_carignano" class="monument-card">
                <img src="../img/monuments/necropol_carignano.jpg" class="card-img" alt="Necropoli Carignano">
                <div class="card-body">
                    <h3>Necropoli Palazzo Carignano</h3>
                    <p>Located just outside the southern walls, this necropolis dates from the 3rd to the 5th century.
                        The site features various burial types, including simple pits, tile-covered "a cappuccina"
                        graves and masonry tombs. Archaeological finds include bronze bracelets with snake-head
                        terminals and coin hoards.</p>
                    <p><b>Discovered in: </b>1989</p>
                    <div class="btn-view">Open in the map <i class="fa fa-arrow-right"></i></div>
                </div>
            </a>

            <a href="../dashboard.php?monument=necropoli_san_carlo" class="monument-card">
                <img src="../img/monuments/necropoli_san_carlo.jpg" class="card-img" alt="Necropoli San Carlo">
                <div class="card-body">
                    <h3>Necropoli Piazza San Carlo</h3>
                    <p>The Piazza San Carlo necropolis dates back to the late 2nd and early 3rd centuries. This small
                        burial ground contained twenty-six inhumation graves; a notable discovery was the grave of an
                        eighteen-month-old child buried with a glass feeding bottle. The site reflects the suburban
                        organization of the city, where funerary areas shared space with agricultural plots just outside
                        the walls.</p>
                    <p><b>Discovered in: </b>2004-2005</p>
                    <div class="btn-view">Open in the map <i class="fa fa-arrow-right"></i></div>
                </div>
            </a>

            <a href="../dashboard.php?monument=tomba_cenisia" class="monument-card">
                <img src="../img/monuments/tomba_cenisia.jpg" class="card-img" alt="Tomba Cenisia">
                <div class="card-body">
                    <h3>Tomba Borgata Cenisia</h3>
                    <p>The tombs in Borgata Cenisia consist of five 2nd-3rd century cremation burials found along the
                        ancient road to Rivoli. The site included brick chests and a masonry tomb containing grave goods
                        such as glass vessels and a coin of Geta from before 211.</p>
                    <p><b>Discovered in: </b>1894</p>
                    <div class="btn-view">Open in the map <i class="fa fa-arrow-right"></i></div>
                </div>
            </a>

            <a href="../dashboard.php?monument=necropoli_fanciullo" class="monument-card">
                <img src="../img/monuments/necropoli_fanciullo.jpg" class="card-img" alt="Necropoli Fanciullo">
                <div class="card-body">
                    <h3>Necropoli e Tomba Fanciullo</h3>
                    <p>The tomb of a child near Porta Palazzo dates from the mid-2nd to the late 3rd century. It
                        featured a masonry structure containing a rare lead sarcophagus, 137 cm long, with the remains
                        of a child buried facing east. Grave goods, including small vases, a cup, and a lamp, were found
                        outside the lead coffin. The discovery suggests the presence of a larger, now destroyed,
                        cemetery area located along the Dora river outside the Roman city walls.</p>
                    <p><b>Discovered in: </b>1893</p>
                    <div class="btn-view">Open in the map <i class="fa fa-arrow-right"></i></div>
                </div>
            </a>

            <a href="../dashboard.php?monument=necropoli_dora" class="monument-card">
                <img src="../img/monuments/necropoli_dora.jpg" class="card-img" alt="Necropoli Dora">
                <div class="card-body">
                    <h3>Necropoli oltre la Dora</h3>
                    <p>The Roman necropolis across the Dora river was a vast burial area used from the 1st to the 4th
                        century. Located north of the ancient city, it contained a high density of graves, funerary
                        steles, and inscriptions. This cemetery followed the typical Roman pattern of lining major
                        suburban roads with monumental and simple tombs, serving as a primary funerary district for the
                        population of Augusta Taurinorum throughout the imperial era.</p>
                    <p><b>Discovered in: </b>19th century, 2011</p>
                    <div class="btn-view">Open in the map <i class="fa fa-arrow-right"></i></div>
                </div>
            </a>

            <a href="../dashboard.php?monument=tomba_cernaia" class="monument-card">
                <img src="../img/monuments/tomba_cernaia.jpg" class="card-img" alt="Tomba Cernaia">
                <div class="card-body">
                    <h3>Tomba Via Cernaia</h3>
                    <p>The Roman tomb in via Cernaia is an inhumation burial dating from the 3rd to the 4th century. It
                        featured a stone slab floor and remains of a "cappuccina" style tile roof. The tomb was largely
                        disturbed and lacked grave goods, except for some fragments.</p>
                    <p><b>Discovered in: </b>1908</p>
                    <div class="btn-view">Open in the map <i class="fa fa-arrow-right"></i></div>
                </div>
            </a>
        </div>
    </div>
    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('active');
        }
    </script>
</body>

</html>