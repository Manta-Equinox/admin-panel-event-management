<?php
session_start();

if (!isset($_SESSION['Aname'])) {
    header('location: index.php');
    exit();
}

require_once "db_connect.php";

function eventName($eid)
{
    if ($eid == 10) return "IPL Auction";
    if ($eid == 11) return "Mathomania";
    if ($eid == 12) return "Python Pro's";
    if ($eid == 13) return "tressure Hunt";
    if ($eid == 14) return "C noobies";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <link rel="shortcut icon"
        href="https://www.google.com/url?sa=i&url=https%3A%2F%2Fen.wikipedia.org%2Fwiki%2FMadras_Institute_of_Technology"
        type="image/x-icon">

    <title>Enigma | View participants</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js"></script>

    <?php include_once('./templates/sidebar.php'); ?>
</head>

<body>

<div class="container" style="padding-top: 150px; padding-left: 50px">

    <div class="row">
        <div class="col-lg-8">

            <form method="post" action="participants.php">
                <div class="mb-3">
                    <select class="form-select" name="event">
                        <option selected>Open this select menu</option>
                        <option value="10">IPL Auction</option>
                        <option value="11">Mathomania</option>
                        <option value="12">Python Pro's</option>
                        <option value="13">Tressure Hunt</option>
                        <option value="14">C noobies</option>
                        <option value="Gaming">Gaming</option>
                        <option value="Math O Mania">Math O Mania</option>
                        <option value="Fandom Quiz">Fandom Quiz</option>
                        <option value="General Quiz">General Quiz</option>
                        <option value="OLPC">OLPC</option>
                        <option value="OSPC">OSPC</option>
                        <option value="Code Marathon">Code Marathon</option>
                        <option value="Reverse Coding">Reverse Coding</option>
                        <option value="Debugging">Debugging</option>
                        <option value="Street Coding">Street Coding</option>
                        <option value="Blind Coding">Blind Coding</option>
                        <option value="Decoding">Decoding</option>
                        <option value="Database">Database</option>
                        <option value="Tech Quiz">Tech Quiz</option>
                        <option value="Coffee with Java">Coffee with Java</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Search</button>
            </form>

            <?php
            if (count($_POST) > 0) {
                $event = $_POST['event'];

                $query1 = "SELECT * FROM participants p
                           JOIN users u ON u.uid = p.uid
                           WHERE p.eid = '$event'";

                $exe1 = mysqli_query($conn, $query1);

                echo "<br>Event Name: " . eventName($event) . "<br>Event ID: " . $event;
            }
            ?>

            <br><br>

            <table class="table">
                <thead>
                    <tr>
                        <th>Participant ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone No</th>
                        <th>Year</th>
                        <th>Department</th>
                    </tr>
                </thead>

                <tbody>
                    <?php
                    if (isset($exe1)) {
                        while ($row1 = mysqli_fetch_array($exe1)) {
                            echo "<tr>
                                <td>{$row1['pid']}</td>
                                <td>{$row1['name']}</td>
                                <td>{$row1['email']}</td>
                                <td>{$row1['phone']}</td>
                                <td>{$row1['year']}</td>
                                <td>{$row1['dept']}</td>
                            </tr>";
                        }
                    }
                    ?>
                </tbody>
            </table>

        </div>
    </div>
</div>

<?php include_once('./templates/footer.php'); ?>

</body>
</html>