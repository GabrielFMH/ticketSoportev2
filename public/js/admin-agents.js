// Toggle edit form visibility for agents
function toggleEdit(id) {
    var editDiv = document.getElementById('edit-' + id);
    editDiv.style.display = editDiv.style.display === 'none' ? 'block' : 'none';
}