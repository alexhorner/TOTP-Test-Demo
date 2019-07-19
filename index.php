<?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    require($_SERVER["DOCUMENT_ROOT"].'/vendor/autoload.php'); //Composer autoload for dependencies

    use OTPHP\TOTP;
    use OTPHP\Factory;

    use Endroid\QrCode\ErrorCorrectionLevel;
    use Endroid\QrCode\LabelAlignment;
    use Endroid\QrCode\QrCode;
    use Endroid\QrCode\Response\QrCodeResponse;

    if(isset($_POST["load"]) && $_POST["load"] != ""){
        header("Location: /?uri=".urlencode(base64_encode($_POST["load"])));
        die();
    }

    if(isset($_GET["label"]) && isset($_GET["issuer"]) && $_GET["label"] != ""){
        $otp = TOTP::create(null, 30);
        $otp->setLabel($_GET["label"]);
        if($_GET["issuer"] != ""){
            $otp->setIssuer($_GET["issuer"]);
        }
        header("Location: /?uri=".urlencode(base64_encode($otp->getProvisioningUri())));
        die();
    }

    if(isset($_GET["uri"]) && $_GET["uri"] != ""){
        $otp = Factory::loadFromProvisioningUri(base64_decode($_GET["uri"]));
    }else{
        $otp = TOTP::create(null, 30);
        $otp->setLabel('demo@example.com');
        header("Location: /?uri=".urlencode(base64_encode($otp->getProvisioningUri())));
        die();
    }

    //URI QR Code
    $qrCode = new QrCode($otp->getProvisioningUri());
    $qrCode->setSize(300);
    $qrCodeEncoded = base64_encode($qrCode->writeString());
?>
<!DOCTYPE html>
<html>
    <head>
        <style>
            * {
                font-family: sans-serif;
            }
        </style>
    </head>
    <body>
        <h1>
            <?php
                $otpNow = $otp->now();
                print(substr($otpNow, 0, 3)." ".substr($otpNow, 3, 6));
            ?>
        </h1>
        <br />
        <?php print('The OTP URI is <input type="text" value="'.$otp->getProvisioningUri().'" /> and should be copied and saved if you want to reproduce this key'); ?><br /><br />
        The below QR code can be scanned with Google Authenticator to add it to the list. Afterwards, this page and Google Authenticator should display the same numbers every refresh<br />
        <img src="data:image/png;base64,<?php print($qrCodeEncoded); ?>" /><br /><br />
        <form method="POST">
            Load OTP URI: <input type="text" placeholder="URI" name="load" required /><input type="submit" value="Load" />
        </form>
        <br /><br />
        <form method="GET">
            Create Custom OTP URI<br />
            Label: <input type="text" name="label" placeholder="demo@example.com" required /><br />
            Issuer: <input type="text" name="issuer" placeholder="Company (optional)" /><br />
            <input type="submit" value="Create" />
        </form>
    </body>
</html>