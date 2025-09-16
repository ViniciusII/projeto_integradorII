<?php
session_start();
session_destroy(); // encerra a sessão
header("Location: ../index.html");
exit;
