    <?php
    // Diálogo de login y registro, disponible en todas las páginas para los visitantes
    if (\Session\session::usuario() === null) {
        require __DIR__ . '/login.php';
    }
    ?>

    <!-- Footer -->
    <footer class="modal-footer justify-content-center mt-auto bg-dark">
        <div class="container justify-content-center">
            <div class="row justify-content-center justify-content-md-start">
                <div class="col text-center"> <a class="text-muted text-decoration-none" href="privacy.php">Privacy policy</a></div>
                <div class="col"> <a class="text-muted text-decoration-none" href="privacy.php#cookies">Cookie policy</a></div>
                <div class="col"> <a class="text-muted text-decoration-none" href="privacy.php#cancellation">Cancellation policy</a></div>
                <div class="col"> <a class="text-muted text-decoration-none" href="contact.php">&copy; Hotel Nube 2021&ndash;<?= date('Y') ?></a></div>
            </div>
        </div>



    </footer>

</body>

</html>