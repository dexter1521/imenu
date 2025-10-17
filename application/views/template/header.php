<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title><?php echo isset($page_title) ? $page_title : 'iMenu'; ?> - Panel de Control</title>

    <!-- Custom fonts for this template-->
    <link href="<?php echo base_url('assets/vendor/fontawesome-free/css/all.min.css'); ?>" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="<?php echo base_url('assets/css/sb-admin-2.min.css'); ?>" rel="stylesheet">
    
    <!-- Animate.css para animaciones de SweetAlert2 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    
    <?php if(isset($extra_css)) echo $extra_css; ?>
    
    <!-- Base URL y CSRF Token para JavaScript -->
    <script>
        window.IMENU_BASE_URL = '<?php echo base_url(); ?>';
        window.IMENU_CSRF_TOKEN_NAME = '<?php echo $this->security->get_csrf_token_name(); ?>';
        window.IMENU_CSRF_TOKEN_VALUE = '<?php echo $this->security->get_csrf_hash(); ?>';
        
        // Compatibilidad con login-admin.js y login-tenant.js
        window.IMENU = window.IMENU || {};
        window.IMENU.csrf = {
            name: '<?php echo $this->security->get_csrf_token_name(); ?>',
            hash: '<?php echo $this->security->get_csrf_hash(); ?>',
            cookie_name: '<?php echo $this->config->item("csrf_cookie_name"); ?>'
        };
    </script>
</head>

<body id="page-top">
    <!-- Page Wrapper -->
    <div id="wrapper">
