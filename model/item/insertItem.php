<?php
require_once('../../inc/config/constants.php');
require_once('../../inc/config/db.php');

$initialStock = 0;
$baseImageFolder = '../../data/item_images/';
$itemImageFolder = '';

if(isset($_POST['itemDetailsItemNumber'])){

    $itemNumber = htmlentities($_POST['itemDetailsItemNumber']);
    $itemName = htmlentities($_POST['itemDetailsItemName']);
    $model = isset($_POST['itemDetailsItemModel']) ? htmlentities($_POST['itemDetailsItemModel']) : ''; // Check if the key exists
    $quantity = htmlentities($_POST['itemDetailsQuantity']);
    // $unitPrice = htmlentities($_POST['itemDetailsUnitPrice']);
    $status = htmlentities($_POST['itemDetailsStatus']);
    $description = htmlentities($_POST['itemDetailsDescription']);

    // Check if mandatory fields are not empty
    if(!empty($itemNumber) && !empty($itemName) && isset($quantity) && isset($model)){

        // Sanitize item number
        $itemNumber = filter_var($itemNumber, FILTER_SANITIZE_STRING);

        // Validate item quantity. It has to be a number
        if(filter_var($quantity, FILTER_VALIDATE_INT) === 0 || filter_var($quantity, FILTER_VALIDATE_INT)){
            // Valid quantity
        } else {
            // Quantity is not a valid number
            echo '<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">&times;</button>Please enter a valid number for quantity</div>';
            exit();
        }

        // Create image folder for uploading images
        $itemImageFolder = $baseImageFolder . $itemNumber;
        if(!is_dir($itemImageFolder)){
            mkdir($itemImageFolder);
        }

        // Calculate the stock values
        $stockSql = 'SELECT stock FROM item WHERE itemNumber=:itemNumber';
        $stockStatement = $conn->prepare($stockSql);
        $stockStatement->execute(['itemNumber' => $itemNumber]);
        if($stockStatement->rowCount() > 0){
            $row = $stockStatement->fetch(PDO::FETCH_ASSOC);
            $quantity = $quantity + $row['stock'];
            echo '<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">&times;</button>Item already exists in DB. Please click the <strong>Update</strong> button to update the details. Or use a different Item Number.</div>';
            exit();
        } else {
            // Item does not exist, therefore, you can add it to DB as a new item
            // Start the insert process
            $insertItemSql = 'INSERT INTO item(itemNumber, itemName, model, stock, status, description) VALUES(:itemNumber, :itemName, :model, :stock, :status, :description)';
            $insertItemStatement = $conn->prepare($insertItemSql);
            $insertItemStatement->execute([
                'itemNumber' => $itemNumber,
                'itemName' => $itemName,
                'model' => $model,
                'stock' => $quantity,
                'status' => $status,
                'description' => $description
            ]);
            echo '<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert">&times;</button>Item added to database.</div>';
            exit();
        }

    } else {
        // One or more mandatory fields are empty. Therefore, display a the error message
        echo '<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">&times;</button>Please enter all fields marked with a (*)</div>';
        exit();
    }
}
?>
