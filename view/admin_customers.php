<?php
require_once "header.php";
requireAdmin();
require_once __DIR__ . "/../model/UserModel.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["delete_id"])) {
    if (csrfCheck()) {
        deleteCustomerById($_POST["delete_id"]);
        $message = "Customer deleted.";
    } else {
        $message = "Security check failed. Please reload the page and try again.";
    }
}

$customers = getAllCustomers();
?>

<a class="back-link" href="admin_dashboard.php">&larr; Back to Dashboard</a>
<h2>Customers</h2>
<?php if ($message != "") { ?><div class="message success"><?php echo clean($message); ?></div><?php } ?>
<table>
    <thead>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Address</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($customers as $customer) { ?>
            <tr>
                <td><?php echo clean($customer["name"]); ?></td>
                <td><?php echo clean($customer["email"]); ?></td>
                <td><?php echo clean($customer["phone"]); ?></td>
                <td><?php echo clean($customer["address"]); ?></td>
                <td><form method="post" class="inline-form" onsubmit="return confirm('Delete customer and all data?')">
                    <?php echo csrfField(); ?>
                    <input type="hidden" name="delete_id" value="<?php echo (int) $customer["id"]; ?>">
                    <button type="submit" class="danger">Delete</button>
                </form></td>
            </tr>
        <?php } ?>
    </tbody>
</table>

<?php require_once "footer.php"; ?>
