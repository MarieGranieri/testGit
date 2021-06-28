<?php


//============================================================
// AFFICHAGE ID + PAGE INNACESSIBLE SI PAS DE SESSION PRESENTE
//============================================================
session_start ();

if(!$_SESSION['identifiant']) {
    return; // le script qui se trouve après n'est plus executé...page blanche
}



//=========================
// CONTROLE FORMULAIRE VIDE
//=========================

$tab_erreur = [];


if ($_POST){
    foreach ($_POST as $name => $value){
        // variable dynamique
        ${$name} = $value;
    
        if (empty($value)){
            $message = $name;
            array_push($tab_erreur, $name);
            //echo ($message .= '<br>');
              
        }
    }
}
    
if(count($tab_erreur) > 0) {
    $erreur = true;
    $message = "Le formulaire ne peut être envoyé ! <br><br>";
    // transforme un array en string
    $message .= "Veuillez renseigner : <br>";
    $message .= implode('<br>', $tab_erreur);
} else {
    $erreur = false;
}



// exo faire une fonction avec les 3 parametres necessaires:
// nom de l'attribut name, TAILLE_MAX_UPLOAD, LARGEUR_MAX_UPLOAD
// il faut que cette fonction retourne un message

$taille_max_upload = 1 * 1024 * 1024;// en octets
define('TAILLE_MAX_UPLOAD', $taille_max_upload); // en octets
define('LARGEUR_MAX_UPLOAD',1024); // en px

if(isset($_POST['envoyer'])) {
    // on recupere la valeur du champ de formulaire
    $max_file_size_html = $_POST['MAX_FILE_SIZE'];
    // on récupère la valeur de php.ini

    function convertBytes( $value ) {
        if ( is_numeric( $value ) ) {
            return $value;
        } else {
            $value_length = strlen($value);
            $qty = substr( $value, 0, $value_length - 1 );
            $unit = strtolower( substr( $value, $value_length - 1 ) );
            switch ( $unit ) {
                case 'k':
                    $qty *= 1024;
                    break;
                case 'm':
                    $qty *= 1048576;
                    break;
                case 'g':
                    $qty *= 1073741824;
                    break;
            }
            return $qty;
        }
    }

    $max_file_size_phpini = convertBytes(ini_get('upload_max_filesize'));



    /////////////////////////////////////
    // traitement de lupload
    if ($_FILES) {

        var_dump($_FILES);
        // recupère le nom de la variable du formulaire
        foreach ($_FILES as $index => $value) {
            $nom_index = $index;
            //var_dump($nom_index);
        }
        // sans la boucle
        //$nom_index = $_FILES['photo_article'];


        $erreur_upload = $_FILES[$nom_index]['error'];
        /* http://www.php.net/manual/en/features.file-upload.errors.php, */

        switch ($erreur_upload) {
            // la taille excede celle définie dans php.ini
            case 1:
                //$erreur = true;
                //$message = "La taille du fichier doit être inférieur à 2Mo";
                break;
            //The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.
            case 2:
                $erreur = true;
                //$message = "La taille du fichier doit être inférieur à " .round(($max_file_size_html/1024),0)." Ko";
                break;
            //The uploaded file was only partially uploaded.
            case 3:
                $erreur = true;
                //$message = "Erreur lors du téléchargement du fichier";
                break;
            case 4:
                $erreur = true;
                //$message = "Aucun fichier n'a été téléchargé";
                break;
            // pas d'erreur
            case 0;
                $taille = $_FILES[$nom_index]['size'];

                if($taille >  TAILLE_MAX_UPLOAD) {
                    $erreur = true;
                    //$message = "La taille du fichier doit être inférieur à " .round((TAILLE_MAX_UPLOAD/1024), 0)." Ko";
                }


                /////////////////////////////
                // verif type du fichier
                /////////////////////////////
                $type_fichier = $_FILES[$nom_index]['type'];
                if (stristr($type_fichier, 'jpg') === FALSE && stristr($type_fichier, 'jpeg') === FALSE) {
                    $erreur = true;
                    //$message = "La photo doit être au format jpg ou jpeg";
                }

                /////////////////////////////
                // verif dimension du fichier
                /////////////////////////////
                $photo = $_FILES[$nom_index]['tmp_name'];
                var_dump(getimagesize($photo));

                // list methode pour tableau: assigne les entrées à des variables
                list($width, $height, $type, $attr) = getimagesize($photo);

                if ($width > LARGEUR_MAX_UPLOAD) {
                    $erreur = true;
                    //$message = 'La photo est trop grande (' . $width . 'px): '.LARGEUR_MAX_UPLOAD.' max autorisé';
                }

        } // fin switch

        /////////////////////////////
        // Enregistrement de la photo
        /////////////////////////////
        if (empty($erreur)) {
            // 1ere utilisation
            $path = '';
            // creation d'un dossier upload s'il n'existe pas
            if (!file_exists($path . 'upload')) {
                $upload = mkdir($path . 'upload/', 0777);
                if (!$upload) {
                    $erreur = true;
                    //$message = 'Dossier non créé!!!';
                } else {
                    $lets_go = true;
                }
                // le dossier upload existe
            } else {
                $lets_go = true;
            }
            //////////////////////////////////////////
            // tout est vérifié on upoload la photo
            /////////////////////////////////////////
            if (isset($lets_go)) {
                //  dossier upload
                $uploaddir = 'upload/';
                // on ne récupère que le nom du fichier
                $nom_photo = basename($_FILES[$nom_index]['name']);
                //var_dump($nom_photo);
                // tout en minuscule
                $nom_photo = strtolower($nom_photo);
                // on supprimme les espaces
                $nom_photo = str_replace(' ', '', $nom_photo);

                // Attention controler aussi les caracteres speciaux



                // on déplace la photo original du dossier tmp dans le dossier upload créer
                $uploadfile = $uploaddir . $nom_photo;
                $move = move_uploaded_file($_FILES[$nom_index]['tmp_name'], $uploadfile);
                // si le deplacement s'est bien passé
                if($move) {
                    //$message = "La photo a bien été téléchargée: ";
                    //$message .= '<a href="upload/';
                    //$message .= $nom_photo;
                    //$message .= '">Voir</a>';
                }else {
                    //$message = 'Problème de téléchargement';
                }
            }
        } // fin empty erreur

    } // fin isset $_FILE

} // fin isset POST


    

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title></title>
    <style>
       label, input {
            display: block;
        }
        form {
            width: 50%;
            margin: auto;
        }
        .erreur {
            background: red;
            color: #fff;
            padding: 50px;
        }
        .valide {
            background: green;
            color: #fff;
            padding: 50px;
        }
    </style>
</head>
<body>


 <!-- si la session existe, on affiche l'ID -->
 <?php if(isset($_SESSION['identifiant'])): ?>
    <p class="<?php echo 'valide'; ?>"><?php echo 'Bonjour, ', $_SESSION['identifiant']; ?></p>
<?php endif; ?>

<!-- affichage du message selon erreur ou validation -->
<?php if(isset($message)): ?>
    <p class="<?php if($erreur) echo 'erreur'; else echo 'valide'; ?>"><?php echo $message; ?></p>
<?php endif; ?>

 <!-- si le cookie existe, on affiche son contenu -->
<?php if(isset($_COOKIE['derniere_visite'])): ?>
    <p><?php $derniere_visite = $_COOKIE['derniere_visite']; echo 'Dernière visite: le '.$derniere_visite ?></p>
<?php endif; ?>



<form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data" name="form_articles" id="form_articles">

    <label for="titre">Titre</label>
    <input type="text" name="titre" id="titre" value="">

    <label for="contenu">Contenu</label>
    <textarea id="contenu" name="contenu"></textarea>

    <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo TAILLE_MAX_UPLOAD ?>" > <!-- facultatif: car facilement contourné -->
    <label for="photo_article">Photo (format .jpg, .jpeg / Taille max: <?php echo round((TAILLE_MAX_UPLOAD/1024), 0) ?>Ko / Largeur max:1024px)</label>
    <input type="file" name="photo_article" id="photo_article">


    <input type="submit" value="Enregistrer la photo" name="envoyer" >

</form>



</body>
</html>

