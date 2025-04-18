
<div class="dashboard-nav mb-4">
    <nav class="nav">
        <a class="nav-link <?php echo ($_GET['page'] === 'medoffice-manager' ? 'active' : ''); ?>" href="admin.php?page=medoffice-manager">
            <i class="fas fa-tachometer-alt"></i> Tableau de bord
        </a>
        <a class="nav-link <?php echo ($_GET['page'] === 'medoffice-patients' ? 'active' : ''); ?>" href="admin.php?page=medoffice-patients">
            <i class="fas fa-users"></i> Patients
        </a>
        <a class="nav-link <?php echo ($_GET['page'] === 'medoffice-consultations' ? 'active' : ''); ?>" href="admin.php?page=medoffice-consultations">
            <i class="fas fa-stethoscope"></i> Consultations
        </a>
        <a class="nav-link <?php echo ($_GET['page'] === 'medoffice-calendar' ? 'active' : ''); ?>" href="admin.php?page=medoffice-calendar">
            <i class="fas fa-calendar-alt"></i> Rendez-vous
        </a>
        <a class="nav-link <?php echo ($_GET['page'] === 'medoffice-settings' ? 'active' : ''); ?>" href="admin.php?page=medoffice-settings">
            <i class="fas fa-cog"></i> Paramètres
        </a>
    </nav>
</div>
