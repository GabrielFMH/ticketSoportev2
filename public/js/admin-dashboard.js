// Simple bar chart using canvas for tickets by status (basic for PHP 5.5, no libs)
window.onload = function() {
    var canvas = document.getElementById('statusChart');
    var ctx = canvas.getContext('2d');
    var statuses = <?php echo json_encode(array_column($ticketsByStatus, 'status')); ?>;
    var counts = <?php echo json_encode(array_column($ticketsByStatus, 'count')); ?>;
    var maxCount = Math.max(...counts);
    var barWidth = canvas.width / statuses.length;
    
    ctx.fillStyle = '#007BFF';
    for (var i = 0; i < statuses.length; i++) {
        var height = (counts[i] / maxCount) * 200;
        ctx.fillRect(i * barWidth, canvas.height - height, barWidth - 10, height);
        ctx.fillStyle = '#000';
        ctx.font = '12px Arial';
        ctx.fillText(statuses[i], i * barWidth + 5, canvas.height - 5);
        ctx.fillText(counts[i], i * barWidth + 5, canvas.height - height - 5);
    }
};