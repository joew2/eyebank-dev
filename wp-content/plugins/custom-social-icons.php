<?php
/*
Plugin Name: Custom Plugin - Social Icons
Description: Plugin to add social icons using shortcode.
*/


// Shortcode function
function social_links_and_phone() {
    ob_start();
    ?>
    <style>
        .social-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            max-width: 200px;
            margin: 0px auto 0px auto;
        }

        .social-channel {
            display: grid;
            justify-content: center;
        }

        .contact-info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            max-width: 300px;
            margin: 10px auto 0px auto;
        }

        .contact-area {
            display: grid;
            justify-content: center;
            font-family: "Average", serif;
            font-size: 22px;
            color: #333333;
        }

        @media screen and (max-width: 600px) {
            .contact-info-grid {
                grid-template-columns: 1fr;
            }
        }
        
    </style>

    <div class="social-grid">

        <div class="social-channel">
            <a href="https://www.facebook.com/eyebankny" draggable="false">
                <img src="https://www.eyedonation.org/wp-content/uploads/2022/06/1_facebook.gif" alt="facebook" width="49" draggable="false">
            </a>
        </div>

        <div class="social-channel">
            <a href="https://www.instagram.com/eyebankny/" draggable="false">
                <img src="https://www.eyedonation.org/wp-content/uploads/2022/06/3_instagram.gif" alt="instagram" width="48" draggable="false">
            </a>
        </div>

        <div class="social-channel">
            <a href="https://www.linkedin.com/company/eyebankny">
                <img src="https://www.eyedonation.org/wp-content/uploads/2024/02/linkedin-social-icon.gif" alt="Linkedin" width="48" draggable="false">
            </a>
        </div>

    </div>

    <!-- Contact info text -->
    <div class="contact-info-grid">
        <div class="contact-area">
            @eyebankny
        </div>
        <div class="contact-area">
            212-742-9000
        </div>
    </div>

    <?php
    return ob_get_clean();
}
add_shortcode( 'social-links-and-phone', 'social_links_and_phone' );
