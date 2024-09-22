jQuery(document).ready(function($) {
    console.log('booking.js chargé');

    // Charger les services dynamiquement via AJAX
    $.ajax({
        url: ajax_object.ajaxurl,
        method: 'POST',
        data: {
            action: 'fetch_services'
        },
        success: function(response) {
            if (response.success) {
                var services = response.data;
                var serviceSelect = $('#service');
                
                serviceSelect.empty(); // Vider les options existantes

                // Ajouter l'option par défaut "Choisir un service"
                serviceSelect.append('<option value="" disabled selected>Choisir un service</option>');

                services.forEach(function(service) {
                    serviceSelect.append(new Option(service.title, service.id));
                    serviceSelect.find('option[value="' + service.id + '"]').attr('data-description', service.description);
                });
            } else {
                console.error('Erreur lors de la récupération des services');
            }
        },
        error: function(xhr, status, error) {
            console.error('Échec de la requête AJAX :', error);
        }
    });

    // Mettre à jour la description du service lors du changement
    $('#service').on('change', function() {
        var selectedOption = $(this).find('option:selected');
        var description = selectedOption.data('description');
        $('#service-description').text(description || 'Veuillez sélectionner un service pour voir les détails.');
    });

    // Navigation entre les étapes avec le bouton "Continuer"
    $('.continue-btn').on('click', function() {
        var currentStep = $(this).closest('.step');
        var nextStep = currentStep.next('.step');
        
        if (nextStep.length) {
            goToStep(nextStep.attr('id').split('-')[1]);
            if (nextStep.attr('id') === 'step-2') {
                initializeFlatpickr(); // Initialiser Flatpickr après le passage à l'étape 2
            }
        }
    });

    // Navigation entre les étapes en cliquant sur les onglets de gauche
    $('.booking-sidebar ul li').on('click', function() {
        var stepNumber = $(this).index() + 1;
        goToStep(stepNumber);
        if (stepNumber === 2) {
            initializeFlatpickr(); // Initialiser Flatpickr si on accède à l'étape 2 via les onglets
        }
    });

    function goToStep(stepNumber) {
        $('.step').removeClass('active').hide();
        $('#step-' + stepNumber).addClass('active').show();

        // Mettre à jour le menu latéral
        $('.booking-sidebar ul li').each(function(index) {
            var circle = $(this).find('.step-circle');
            if (index + 1 < stepNumber) {
                circle.addClass('checked').removeClass('active future').text('✔');
            } else if (index + 1 === stepNumber) {
                circle.addClass('active').removeClass('checked future').text(stepNumber);
            } else {
                circle.addClass('future').removeClass('checked active').text(index + 1);
            }
        });
    }

    // Initialiser Flatpickr pour le calendrier et les heures
    function initializeFlatpickr() {
        flatpickr("#appointment_date", {
            dateFormat: "Y-m-d",
            inline: true,
            minDate: "today",
            onChange: function(selectedDates, dateStr, instance) {
                $('#appointment_date').hide();
                $('#appointment_time').show();

                $.ajax({
                    url: ajax_object.ajaxurl,
                    method: 'POST',
                    data: {
                        action: 'get_disponibilites',
                        date: dateStr
                    },
                    success: function(response) {
                        var timeSelect = $('#appointment_time');
                        timeSelect.empty();
                        
                        if (response.success) {
                            var times = response.data;
                            if (times.length === 0) {
                                timeSelect.append(new Option('Aucune disponibilité', '', true, true)).prop('disabled', true);
                            } else {
                                times.forEach(function(time) {
                                    timeSelect.append(new Option(time, time));
                                });
                                timeSelect.prop('disabled', false);
                            }
                        } else {
                            console.error('Pas de disponibilités trouvées');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Erreur lors de la requête AJAX :', error);
                    }
                });
            }
        });

        flatpickr("#appointment_time", {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",
            time_24hr: true
        });

        $('#appointment_time').hide();
    }
});
