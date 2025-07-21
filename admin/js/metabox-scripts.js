/**
 * Lógica JavaScript para o metabox de detalhes do imóvel no painel de administração.
 * Gerencia a galeria de imagens e o autocompletar de endereço do Google Maps.
 */

jQuery(document).ready(function($) {

    // =========================================================================
    // GALERIA DE IMAGENS
    // =========================================================================

    var mediaUploader;

    $('#li_galeria_upload_button').on('click', function(e) {
        e.preventDefault();

        // Se o uploader de mídia já existe, abre-o.
        if (mediaUploader) {
            mediaUploader.open();
            return;
        }

        // Cria uma nova instância do uploader de mídia.
        mediaUploader = wp.media({
            title: 'Selecione ou Carregue Imagens para a Galeria',
            button: {
                text: 'Adicionar à Galeria'
            },
            multiple: true // Permite selecionar múltiplos arquivos
        });

        // Quando arquivos são selecionados, processa-os.
        mediaUploader.on('select', function() {
            var attachments = mediaUploader.state().get('selection').toJSON();
            var currentGalleryIds = $('#li_galeria_ids').val().split(',').filter(Boolean).map(Number); // Filtrar valores vazios
            var newImageHtml = '';

            $.each(attachments, function(index, attachment) {
                // Adiciona o ID apenas se ainda não estiver na lista
                if ($.inArray(attachment.id, currentGalleryIds) === -1) {
                    currentGalleryIds.push(attachment.id);
                    var thumbnailUrl = (typeof attachment.sizes.thumbnail !== 'undefined') ? attachment.sizes.thumbnail.url : attachment.url;
                    newImageHtml += '<div class="li-gallery-item" data-id="' + attachment.id + '">';
                    newImageHtml += '<img src="' + thumbnailUrl + '">';
                    newImageHtml += '<button type="button" class="li-remove-image">×</button>';
                    newImageHtml += '</div>';
                }
            });

            // Atualiza o campo hidden e a pré-visualização.
            $('#li_galeria_ids').val(currentGalleryIds.join(','));
            $('#li_galeria_preview').append(newImageHtml);
        });

        // Abre o uploader de mídia.
        mediaUploader.open();
    });

    // Remover imagem da galeria
    $('#li_galeria_preview').on('click', '.li-remove-image', function() {
        var $itemToRemove = $(this).closest('.li-gallery-item');
        var idToRemove = $itemToRemove.data('id');
        var currentGalleryIds = $('#li_galeria_ids').val().split(',').filter(Boolean).map(Number); // Filtrar valores vazios

        // Remove o ID do array
        currentGalleryIds = $.grep(currentGalleryIds, function(value) {
            return value != idToRemove;
        });

        // Atualiza o campo hidden e remove o item da pré-visualização.
        $('#li_galeria_ids').val(currentGalleryIds.join(','));
        $itemToRemove.remove();
    });

    // Arrastar e soltar para reordenar imagens (usando jQuery UI Sortable)
    if (typeof $.fn.sortable !== 'undefined') {
        $('#li_galeria_preview').sortable({
            items: '.li-gallery-item',
            cursor: 'move',
            axis: 'x', // Permite arrastar apenas na horizontal (ou 'y' para vertical, false para ambos)
            tolerance: 'pointer',
            update: function() {
                var newOrder = [];
                $('#li_galeria_preview .li-gallery-item').each(function() {
                    newOrder.push($(this).data('id'));
                });
                $('#li_galeria_ids').val(newOrder.join(','));
            }
        });
    }

    // =========================================================================
    // AUTOCOMPLETAR ENDEREÇO (GOOGLE MAPS PLACES API)
    // =========================================================================
    if (typeof google !== 'undefined' && typeof google.maps !== 'undefined' && typeof google.maps.places !== 'undefined') {
        var autocompleteInput = document.getElementById('li_autocomplete_address');
        if (autocompleteInput) {
            var autocomplete = new google.maps.places.Autocomplete(autocompleteInput, {
                types: ['address'],
                componentRestrictions: {
                    'country': ['br']
                } // Restringe a busca ao Brasil
            });

            autocomplete.addListener('place_changed', function() {
                var place = autocomplete.getPlace();

                if (!place.geometry) {
                    // Usuário inseriu o nome de um Lugar mas não selecionou uma das sugestões
                    return;
                }

                // Limpa os campos antes de preencher
                $('#li_rua').val('');
                $('#li_bairro').val('');
                $('#li_cidade').val('');
                $('#li_estado').val('');
                $('#li_cep').val('');

                var streetNumber = '';
                var streetName = '';

                for (var i = 0; i < place.address_components.length; i++) {
                    var addressType = place.address_components[i].types[0];

                    if (addressType === 'street_number') {
                        streetNumber = place.address_components[i].long_name;
                    } else if (addressType === 'route') {
                        streetName = place.address_components[i].long_name;
                    } else if (addressType === 'sublocality_level_1' || addressType === 'sublocality') {
                        $('#li_bairro').val(place.address_components[i].long_name);
                    } else if (addressType === 'administrative_area_level_2') {
                        $('#li_cidade').val(place.address_components[i].long_name);
                    } else if (addressType === 'administrative_area_level_1') {
                        $('#li_estado').val(place.address_components[i].short_name); // 'SP' para São Paulo
                    } else if (addressType === 'postal_code') {
                        $('#li_cep').val(place.address_components[i].long_name);
                    }
                }

                // Concatena rua e número, se ambos existirem
                var fullStreet = streetName;
                if (streetNumber) {
                    fullStreet += ', ' + streetNumber;
                }
                $('#li_rua').val(fullStreet);
            });
        }
    } else {
        console.warn('Google Maps API ou Places Library não carregada. Autocompletar de endereço desativado.');
    }

    // =========================================================================
    // MÁSCARAS DE INPUT (se necessário, você pode adicionar uma biblioteca como jQuery Mask Plugin)
    // =========================================================================

    // Exemplo de máscara para CEP (se usar jQuery Mask Plugin)
    // if (typeof $.fn.mask !== 'undefined') {
    //     $('#li_cep').mask('00000-000');
    //     $('#li_valor_venda, #li_valor_aluguel').mask('000.000.000.000.000,00', {reverse: true});
    // }

    // Implementação manual simples para valores monetários (melhor usar lib de máscara)
    $('input[name="li_valor_venda"], input[name="li_valor_aluguel"]').on('keyup', function() {
        var value = $(this).val();
        // Remove tudo que não for dígito e vírgula
        value = value.replace(/\D/g, '');
        // Se a vírgula foi digitada, mantenha-a
        if (value.indexOf(',') === -1 && $(this).val().indexOf(',') > -1) {
            value = value.slice(0, -2) + ',' + value.slice(-2);
        } else if (value.length > 2) {
            // Adiciona a vírgula antes dos dois últimos dígitos para centavos
            value = value.slice(0, -2) + ',' + value.slice(-2);
        }
        // Adiciona pontos para milhares
        value = value.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.');
        $(this).val(value);
    });
});