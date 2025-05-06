// Toggle Sidebar on Mobile
document.querySelector('.menu-toggle').addEventListener('click', function () {
    document.querySelector('.sidebar').classList.toggle('active');
});

// Initialize Charts
document.addEventListener('DOMContentLoaded', function () {
    // User Growth Chart
    const userGrowthCtx = document.getElementById('userGrowthChart').getContext('2d');
    const userGrowthChart = new Chart(userGrowthCtx, {
        type: 'line',
        data: {
            labels: ['1 May', '5 May', '10 May', '15 May', '20 May', '25 May', '30 May'],
            datasets: [{
                label: 'New Users',
                data: [12, 19, 15, 27, 22, 30, 42],
                borderColor: 'rgba(108, 92, 231, 1)',
                backgroundColor: 'rgba(108, 92, 231, 0.1)',
                tension: 0.4,
                fill: true,
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });

    // Transaction Distribution Chart
    const transactionDistributionCtx = document.getElementById('transactionDistributionChart').getContext('2d');
    const transactionDistributionChart = new Chart(transactionDistributionCtx, {
        type: 'doughnut',
        data: {
            labels: ['USDT Deposits', 'INR Deposits', 'Withdrawals', 'Transfers', 'Trades'],
            datasets: [{
                data: [35, 25, 20, 15, 5],
                backgroundColor: [
                    'rgba(108, 92, 231, 0.8)',
                    'rgba(162, 155, 254, 0.8)',
                    'rgba(255, 71, 87, 0.8)',
                    'rgba(46, 213, 115, 0.8)',
                    'rgba(255, 165, 2, 0.8)'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right'
                }
            },
            cutout: '70%'
        }
    });

    // Timeframe selector for user growth chart
    document.getElementById('userGrowthTimeframe').addEventListener('change', function () {
        // In a real app, you would fetch new data based on the timeframe
        // For this demo, we'll just show a loading spinner
        showLoading();
        setTimeout(() => {
            hideLoading();
            showToast('Chart updated with new timeframe data', 'success');
        }, 1000);
    });
});

// Modal Functions
function openModal(modalId) {
    document.getElementById(modalId).style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
    document.body.style.overflow = 'auto';
}

// Form Submission
function submitAddUserForm() {
    showLoading();
    // Simulate API call
    setTimeout(() => {
        hideLoading();
        closeModal('addUserModal');
        showToast('User added successfully!', 'success');
        // Reset form
        document.getElementById('addUserForm').reset();
    }, 1500);
}

// Toast Notification
function showToast(message, type) {
    const toastContainer = document.getElementById('toastContainer');
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `
    <span class="material-icons-round">${type === 'success' ? 'check_circle' : type === 'error' ? 'error' : type === 'warning' ? 'warning' : 'info'}</span>
    <span>${message}</span>
    <span class="material-icons-round toast-close">close</span>
`;

    toastContainer.appendChild(toast);

    // Auto remove after 5 seconds
    setTimeout(() => {
        toast.remove();
    }, 5000);

    // Close button
    toast.querySelector('.toast-close').addEventListener('click', () => {
        toast.remove();
    });
}

// Loading Spinner
function showLoading() {
    document.getElementById('loadingSpinner').style.display = 'flex';
}

function hideLoading() {
    document.getElementById('loadingSpinner').style.display = 'none';
}

// Dark Mode Toggle
const icon = document.getElementById('darkModeIcon');
const body = document.body;

// Load preference
if (localStorage.getItem('darkMode') === 'enabled') {
    body.classList.add('dark-mode');
    icon.textContent = 'light_mode';
}

document.getElementById('darkModeToggle').addEventListener('click', function () {
    body.classList.toggle('dark-mode');
    const isDark = body.classList.contains('dark-mode');
    icon.textContent = isDark ? 'light_mode' : 'dark_mode';
    localStorage.setItem('darkMode', isDark ? 'enabled' : 'disabled');
});

// Placeholder functions for table actions
function viewUserDetails(userId) {
    showToast(`Viewing details for user ${userId}`, 'info');
}

function editUser(userId) {
    showToast(`Editing user ${userId}`, 'info');
}

function messageUser(userId) {
    showToast(`Messaging user ${userId}`, 'info');
}

function viewTransaction(txId) {
    showToast(`Viewing transaction ${txId}`, 'info');
}

function downloadReceipt(txId) {
    showToast(`Downloading receipt for ${txId}`, 'success');
}

// Simulate page loading
window.addEventListener('load', function () {
    setTimeout(() => {
        hideLoading();
    }, 1000);
});


function goToNextPage() {
    window.location.href = "all-user.html"; // Replace with your actual page URL
}

function goToNextPage2() {
    window.location.href = "KYC-Approvals.html"; // Replace with your actual page URL
}

function goToNextPage3() {
    window.location.href = "login-history.html"; // Replace with your actual page URL
}

function goToNextPage4() {
    window.location.href = "USDT-Deposits.html"; // Replace with your actual page URL
}

function goToNextPage5() {
    window.location.href = "INR-Withdrawals.html"; // Replace with your actual page URL
}

function goToNextPage6() {
    window.location.href = "Rate-Management.html"; // Replace with your actual page URL
}

function goToNextPage7() {
    window.location.href = "Transaction-Reports.html"; // Replace with your actual page URL
}