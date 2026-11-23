<?php
require_once "header.php";
requireAdmin();
require_once __DIR__ . "/../model/OrderModel.php";

$orders = getAllOrders();
?>

<a class="back-link" href="admin_dashboard.php">&larr; Back to Dashboard</a>
<h2>Order List</h2>
<div id="orderMsg"></div>
<table>
    <thead>
        <tr>
            <th>Order</th>
            <th>Customer</th>
            <th>Total</th>
            <th>Status</th>
            <th>Date</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($orders as $order) { ?>
            <tr>
                <td>#<?php echo $order["id"]; ?></td>
                <td><?php echo clean($order["customer_name"]); ?></td>
                <td>BDT <?php echo clean($order["total_amount"]); ?></td>
                <td><span class="status-pill status-<?php echo clean($order["status"]); ?>" id="status<?php echo $order["id"]; ?>"><?php echo clean($order["status"]); ?></span></td>
                <td><?php echo clean($order["order_date"]); ?></td>
                <td class="actions" id="actions<?php echo $order["id"]; ?>"></td>
            </tr>
        <?php } ?>
    </tbody>
</table>
<?php if (count($orders) == 0) { ?><p>No orders yet.</p><?php } ?>

<script>
var orderStatuses = <?php
    $map = array();
    foreach ($orders as $o) { $map[$o["id"]] = $o["status"]; }
    echo json_encode($map);
?>;

function renderActions(orderId) {
    let status = orderStatuses[orderId];
    let cell = document.getElementById("actions" + orderId);
    let html = "";

    if (status == "pending") {
        html += "<button onclick=\"changeStatus(" + orderId + ", 'confirmed')\">Confirm</button> ";
    }
    if (status == "pending" || status == "confirmed") {
        html += "<button class='danger' onclick=\"changeStatus(" + orderId + ", 'rejected')\">Reject</button>";
    }
    cell.innerHTML = html == "" ? "<span class='muted-text'>No action needed</span>" : html;
}

function showMessage(text, ok) {
    let box = document.getElementById("orderMsg");
    box.innerHTML = "";
    let p = document.createElement("p");
    p.className = ok ? "message success" : "message";
    p.textContent = text;
    box.appendChild(p);
}

function changeStatus(orderId, status) {
    let data = new FormData();
    data.set("csrf_token", document.querySelector('meta[name="csrf-token"]').content);
    data.append("order_id", orderId);
    data.append("status", status);

    fetch("../ajax/order_status.php", { method: "POST", body: data })
    .then(res => res.json())
    .then(data => {
        if (data.status == "success") {
            orderStatuses[orderId] = status;
            let pill = document.getElementById("status" + orderId);
            pill.innerText = status;
            pill.className = "status-pill status-" + status;
            renderActions(orderId);
            showMessage("Order #" + orderId + " " + status + ".", true);
        } else {
            showMessage(data.message || "Update failed.", false);
        }
    })
    .catch(() => showMessage("Could not reach the server.", false));
}

Object.keys(orderStatuses).forEach(renderActions);
</script>

<?php require_once "footer.php"; ?>
