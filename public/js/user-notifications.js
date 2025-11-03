// User notifications JavaScript functionality
function toggleNotifications() {
    var dropdown = document.getElementById('notificationDropdown');
    dropdown.style.display = dropdown.style.display === 'none' || dropdown.style.display === '' ? 'block' : 'none';
}

// Close notifications when clicking outside
document.addEventListener('click', function(event) {
    var notificationBell = document.querySelector('.notification-bell');
    var dropdown = document.getElementById('notificationDropdown');
    
    if (notificationBell && dropdown && !notificationBell.contains(event.target)) {
        dropdown.style.display = 'none';
    }
});

function viewTicket(ticketId) {
    window.location.href = '?controller=ticket&action=view&id=' + ticketId;
}