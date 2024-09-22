<div class="booking-container">
    <div class="booking-sidebar">
        <ul>
            <li class="step-1 active">
                <span class="step-circle checked">✔</span> Services
            </li>
            <li class="step-2">
                <span class="step-circle">2</span> Date & heure
            </li>
            <li class="step-3">
                <span class="step-circle">3</span> Vos informations
            </li>
        </ul>
    </div>
    <div class="booking-content">
        <!-- Étape 1 : Sélection du service -->
        <div class="step active" id="step-1">
            <h2>Choix du service</h2>
            <label for="service">Choisir un service:</label>
            <select name="service" id="service">
                <option value="" disabled selected>Choisir un service</option>
                <?php
                // Dynamique : Récupération des services
                $services = new WP_Query(array('post_type' => 'service'));
                if ($services->have_posts()) :
                    while ($services->have_posts()) : $services->the_post();
                        $service_id = get_the_ID();
                        $service_title = get_the_title();
                        $service_description = get_the_content();
                        echo "<option value='{$service_id}' data-description='{$service_description}'>{$service_title}</option>";
                    endwhile;
                    wp_reset_postdata();
                endif;
                ?>
            </select>
            <div id="service-description" class="service-description">
                <!-- La description du service sera mise à jour ici -->
            </div>
            <button class="continue-btn" onclick="goToStep(2)">Continuer</button>
        </div>

        <!-- Étape 2 : Sélection de la date et de l'heure -->
        <div class="step" id="step-2" style="display:none;">
            <h2>Date & heure</h2>
            <label for="appointment_date">Choisir une date:</label>
            <input type="text" name="appointment_date" id="appointment_date"> <!-- Le champ Flatpickr pour le calendrier -->
            <label for="appointment_time">Choisir une heure:</label>
            <select name="appointment_time" id="appointment_time" disabled>
                <!-- Les créneaux horaires disponibles seront remplis dynamiquement ici -->
            </select>
            <button class="continue-btn" onclick="goToStep(3)">Continuer</button>
        </div>

        <!-- Étape 3 : Vos informations -->
        <div class="step" id="step-3" style="display:none;">
            <h2>Vos informations</h2>
            <label for="client_name">Votre nom:</label>
            <input type="text" name="client_name" id="client_name" placeholder="Votre nom">
            <label for="client_email">Votre email:</label>
            <input type="email" name="client_email" id="client_email" placeholder="Votre email">
            <button class="continue-btn">Soumettre</button>
        </div>
    </div>
</div>
