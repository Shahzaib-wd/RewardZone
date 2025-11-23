<?php
$hash = '$2y$12$1VLVqkBdDySKM3q7mtBH7.MDJy3z/EF8po84lByiPwl2R1x9WVPoS'; 
$input = 'hackers_ka_dada';  // yahan wo password daalna jo check karna hai

if (password_verify($input, $hash)) {
    echo "Match hogaya!";
} else {
    echo "Match nahi hoa!";
}
?>
