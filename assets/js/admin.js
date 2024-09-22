jQuery(document).ready(function($) {
    let index = 1; // Indice pour identifier chaque créneau horaire unique
    console.log('admin.js chargé correctement.');

    // Ajouter un bouton pour ajouter des créneaux horaires
    $('#add-creneau').on('click', function() {
        // HTML pour un nouveau créneau horaire (date + heure de début + heure de fin)
        const newField = `
            <div class="creneau" id="creneau_${index}">
                <label>Date: <input type="date" name="disponibilites[${index}][date]" required /></label>
                <label>Heure de début: <input type="time" name="disponibilites[${index}][start_time]" required /></label>
                <label>Heure de fin: <input type="time" name="disponibilites[${index}][end_time]" required /></label>
                <button type="button" class="remove-creneau button button-secondary" data-id="${index}">Supprimer</button>
            </div>
        `;
        
        // Ajouter le nouveau créneau avant le bouton "Ajouter un créneau"
        $('#add-creneau').before(newField);
        
        index++; // Incrémenter l'indice pour le prochain créneau

        // Ajouter un événement pour supprimer le créneau
        $('.remove-creneau').on('click', function() {
            const removeId = $(this).data('id');
            $(`#creneau_${removeId}`).remove();
        });
    });
});
