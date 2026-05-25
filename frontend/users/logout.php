<script>
    localStorage.removeItem('user');
    fetch('http://localhost/emploi_du_temps/backend/controllers/users/logout.php', {
        method: 'POST',
        credentials: 'include'
    }).finally(() => {
        location.replace('../users/login.php');
    });
</script>
