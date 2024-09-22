<?php

// Ajouter la prise en charge du logo personnalisé
function amelia_like_theme_setup() {
    add_theme_support('custom-logo');
}
add_action('after_setup_theme', 'amelia_like_theme_setup');

// Ajouter le calendrier Flatpickr
function enqueue_flatpickr() {
    wp_enqueue_script('flatpickr', 'https://cdn.jsdelivr.net/npm/flatpickr', array(), null, true);
    wp_enqueue_style('flatpickr-style', 'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css');
}
add_action('wp_enqueue_scripts', 'enqueue_flatpickr');

// Enqueue des styles et des scripts
function amelia_like_theme_scripts() {
    wp_enqueue_style('amelia-booking-css', get_template_directory_uri() . '/assets/css/booking.css');
    wp_enqueue_script('amelia-booking-js', get_template_directory_uri() . '/assets/js/booking.js', array('jquery'), null, true);
}
add_action('wp_enqueue_scripts', 'amelia_like_theme_scripts');

// Mise en cache des fichiers JS
function lamaisonzelles_enqueue_cached_scripts() {
    wp_enqueue_script('admin-js', get_template_directory_uri() . '/assets/js/admin.js', array('jquery'), filemtime(get_template_directory() . '/assets/js/admin.js'), true);
    wp_enqueue_script('booking-js', get_template_directory_uri() . '/assets/js/booking.js', array('jquery'), filemtime(get_template_directory() . '/assets/js/booking.js'), true);
}
add_action('wp_enqueue_scripts', 'lamaisonzelles_enqueue_cached_scripts');

// Enregistrer le Custom Post Type pour les Services
function create_service_post_type() {
    register_post_type('service', array(
        'labels' => array(
            'name' => __('Services'),
            'singular_name' => __('Service'),
        ),
        'public' => true,
        'has_archive' => true,
        'rewrite' => array('slug' => 'services'),
        'supports' => array('title', 'editor'),
    ));
}
add_action('init', 'create_service_post_type');

// Enregistrer le Custom Post Type pour les Employés
function create_employee_post_type() {
    register_post_type('employee', array(
        'labels' => array(
            'name' => __('Employees'),
            'singular_name' => __('Employee'),
        ),
        'public' => true,
        'has_archive' => true,
        'rewrite' => array('slug' => 'employees'),
        'supports' => array('title', 'editor'),
    ));
}
add_action('init', 'create_employee_post_type');

// Charger les services dynamiquement via AJAX
function fetch_services_via_ajax() {
    $services = new WP_Query(array('post_type' => 'service'));
    $service_data = array();

    if ($services->have_posts()) :
        while ($services->have_posts()) : $services->the_post();
            $service_id = get_the_ID();
            $service_title = get_the_title();
            $service_description = get_the_content();
            $service_data[] = array(
                'id' => $service_id,
                'title' => $service_title,
                'description' => $service_description,
            );
        endwhile;
        wp_reset_postdata();
    endif;

    wp_send_json_success($service_data);
}
add_action('wp_ajax_nopriv_fetch_services', 'fetch_services_via_ajax');
add_action('wp_ajax_fetch_services', 'fetch_services_via_ajax');

// Localiser l'URL d'Ajax pour les scripts JavaScript
function lamaisonzelles_enqueue_scripts() {
    wp_enqueue_script('booking-js', get_template_directory_uri() . '/assets/js/booking.js', array('jquery'), null, true);
    wp_localize_script('booking-js', 'ajax_object', array('ajaxurl' => admin_url('admin-ajax.php')));
}
add_action('wp_enqueue_scripts', 'lamaisonzelles_enqueue_scripts');

// Gestion des disponibilités dans le back-end avec ACF (remplace l'ancienne gestion JSON)
function lamaisonzelles_add_admin_menu() {
    add_menu_page(
        'Gestion des Disponibilités',    // Titre de la page
        'Disponibilités',                // Titre du menu
        'manage_options',                // Capacité requise pour accéder
        'disponibilites',                // Slug du menu
        'lamaisonzelles_disponibilites_page', // Fonction callback pour afficher la page
        'dashicons-calendar-alt',        // Icône du menu
        26                               // Position dans le menu
    );
}
add_action('admin_menu', 'lamaisonzelles_add_admin_menu');

// Page de gestion des disponibilités
function lamaisonzelles_disponibilites_page() {
    // Sauvegarder les créneaux horaires spécifiques
    if (isset($_POST['submit'])) {
        $new_slot = array(
            'date' => sanitize_text_field($_POST['date']),
            'start_time' => sanitize_text_field($_POST['start_time']),
            'end_time' => sanitize_text_field($_POST['end_time']),
        );
        $disponibilites = get_option('lamaisonzelles_plages', []);
        $disponibilites[] = $new_slot;
        update_option('lamaisonzelles_plages', $disponibilites);
    }

    // Suppression d'une disponibilité
    if (isset($_POST['delete_slot'])) {
        $index = (int)$_POST['delete_slot'];
        $disponibilites = get_option('lamaisonzelles_plages', []);
        if (isset($disponibilites[$index])) {
            unset($disponibilites[$index]);
            update_option('lamaisonzelles_plages', $disponibilites);
        }
    }

    // Gestion des modifications
    if (isset($_POST['edit_slot'])) {
        $index = (int)$_POST['edit_slot'];
        $disponibilites = get_option('lamaisonzelles_plages', []);
        if (isset($disponibilites[$index])) {
            $slot_to_edit = $disponibilites[$index];
            $date = esc_html($slot_to_edit['date']);
            $start_time = esc_html($slot_to_edit['start_time']);
            $end_time = esc_html($slot_to_edit['end_time']);
        }
    }

    // Récupérer les disponibilités existantes
    $disponibilites = get_option('lamaisonzelles_plages', []);
    ?>
    <div class="wrap">
        <h1>Gestion des Disponibilités</h1>
        
        <!-- Conteneur des disponibilités -->
        <div class="disponibilites-container">
        
            <!-- Formulaire pour ajouter ou éditer une plage horaire -->
            <form method="post">
                <label for="date">Date :</label>
                <input type="date" name="date" value="<?php echo isset($date) ? $date : ''; ?>" required>
                <label for="start_time">Heure de début :</label>
                <input type="time" name="start_time" value="<?php echo isset($start_time) ? $start_time : ''; ?>" required>
                <label for="end_time">Heure de fin :</label>
                <input type="time" name="end_time" value="<?php echo isset($end_time) ? $end_time : ''; ?>" required>
                <br>
                <?php if (isset($slot_to_edit)): ?>
                    <input type="hidden" name="edit_slot" value="<?php echo $index; ?>">
                    <input type="submit" name="submit_edit" value="Mettre à jour la disponibilité" class="button button-primary">
                <?php else: ?>
                    <input type="submit" name="submit" value="Ajouter la disponibilité" class="button button-primary">
                <?php endif; ?>
            </form>

            <!-- Tableau des plages horaires existantes -->
            <h2>Disponibilités actuelles</h2>
            <table class="widefat">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Heure de début</th>
                        <th>Heure de fin</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($disponibilites)) : ?>
                        <?php foreach ($disponibilites as $index => $slot) : ?>
                            <tr>
                                <td><?php echo esc_html($slot['date']); ?></td>
                                <td><?php echo esc_html($slot['start_time']); ?></td>
                                <td><?php echo esc_html($slot['end_time']); ?></td>
                                <td>
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="delete_slot" value="<?php echo $index; ?>">
                                        <input type="submit" value="Supprimer" class="button button-secondary">
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr><td colspan="4">Aucune disponibilité ajoutée pour l'instant.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        
        </div> <!-- fin de .disponibilites-container -->

        <!-- Gestion des heures d'ouverture générales -->
        <?php
        if (isset($_POST['opening_hours_submit'])) {
            $opening_hours = array_map('sanitize_text_field', $_POST['opening_hours']);
            update_option('lamaisonzelles_opening_hours', $opening_hours);
        }
        $opening_hours = get_option('lamaisonzelles_opening_hours', []);
        ?>
        <h2>Heures d'ouverture générales</h2>
        <form method="post">
            <?php 
            $days = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
            foreach ($days as $day) :
                $day_key = strtolower($day);
                ?>
                <div>
                    <label><?php echo $day; ?> :</label>
                    <input type="text" name="opening_hours[<?php echo $day_key; ?>]" value="<?php echo esc_attr($opening_hours[$day_key] ?? ''); ?>">
                </div>
            <?php endforeach; ?>
            <br>
            <input type="submit" name="opening_hours_submit" value="Mettre à jour les heures d'ouverture" class="button button-primary">
        </form>
    </div>
    <?php
}

// Action AJAX pour récupérer les disponibilités
add_action('wp_ajax_get_disponibilites', 'lamaisonzelles_get_disponibilites');
add_action('wp_ajax_nopriv_get_disponibilites', 'lamaisonzelles_get_disponibilites');

function lamaisonzelles_get_disponibilites() {
    $date = isset($_POST['date']) ? sanitize_text_field($_POST['date']) : '';  // Date sélectionnée
    $disponibilites = get_option('lamaisonzelles_plages', []); // Récupère les disponibilités

    // Filtrer les créneaux horaires pour la date donnée
    $disponibilites_par_date = array_filter($disponibilites, function($plage) use ($date) {
        return $plage['date'] === $date;
    });

    // Extraire uniquement les créneaux horaires pour les envoyer au front-end
    $times = array_map(function($plage) {
        return $plage['start_time'] . ' - ' . $plage['end_time'];
    }, $disponibilites_par_date);

    if (!empty($times)) {
        wp_send_json_success($times);  // Envoie les créneaux horaires au front-end
    } else {
        wp_send_json_error('Aucune disponibilité pour cette date');  // Message si aucune dispo
    }
}

// Ajouter des sous-menus pour la gestion des réservations, clients, paiements, réglages
function lamaisonzelles_add_full_admin_menu() {
    add_submenu_page('disponibilites', 'Réservations', 'Réservations', 'manage_options', 'reservations', 'lamaisonzelles_reservations_page');
    add_submenu_page('disponibilites', 'Clients', 'Clients', 'manage_options', 'clients', 'lamaisonzelles_clients_page');
    add_submenu_page('disponibilites', 'Paiements', 'Paiements', 'manage_options', 'paiements', 'lamaisonzelles_paiements_page');
    add_submenu_page('disponibilites', 'Réglages', 'Réglages', 'manage_options', 'reglages', 'lamaisonzelles_reglages_page');
}
add_action('admin_menu', 'lamaisonzelles_add_full_admin_menu');

// Charger le fichier JavaScript pour l'administration
function lamaisonzelles_enqueue_admin_scripts() {
    if (is_admin()) {
        wp_enqueue_script('admin-js', get_template_directory_uri() . '/assets/js/admin.js', array('jquery'), null, true);
        wp_localize_script('admin-js', 'ajax_object', array('ajaxurl' => admin_url('admin-ajax.php')));
    }
}
add_action('admin_enqueue_scripts', 'lamaisonzelles_enqueue_admin_scripts');

// Charger les scripts uniquement dans le front-end
function lamaisonzelles_enqueue_frontend_scripts() {
    if (!is_admin()) {
        wp_enqueue_script('booking-js', get_template_directory_uri() . '/assets/js/booking.js', array('jquery'), null, true);
        wp_localize_script('booking-js', 'ajax_object', array('ajaxurl' => admin_url('admin-ajax.php')));
    }
}
add_action('wp_enqueue_scripts', 'lamaisonzelles_enqueue_frontend_scripts');

// Sauvegarder les créneaux horaires dynamiques
add_action('acf/save_post', 'save_disponibilites_fields');
function save_disponibilites_fields($post_id) {
    if (isset($_POST['disponibilites']) && !empty($_POST['disponibilites'])) {
        update_field('disponibilites', $_POST['disponibilites'], $post_id);
    }
}
?>
