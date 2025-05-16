<?php
include "db.php";

// This is a simulation: normally you'd call an API or node
$new_tx = [
    'tx_hash' => '0x' . bin2hex(random_bytes(16)),
    'wallet_address' => 'TXYZ...' . rand(1000,9999),
    'amount' => rand(10, 100),
    'confirmations' => 3,
    'timestamp' => date('Y-m-d H:i:s')
];

// Insert new simulated transaction
$stmt = $conn->prepare("INSERT INTO usdt_deposits (tx_hash, wallet_address, amount, confirmations, timestamp) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("ssdis", $new_tx['tx_hash'], $new_tx['wallet_address'], $new_tx['amount'], $new_tx['confirmations'], $new_tx['timestamp']);
$stmt->execute();

echo "New deposit inserted.\n";
