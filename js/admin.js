jQuery(document).ready(function($) {
    // Variáveis para os elementos do DOM
    var $uploadButton = $('.upload_image_button');
    var $removeButton = $('.remove_image_button');
    var $imageContainer = $('.custom-img-container');
    var $imageInput = $('.custom-tab-image-id');
    
    // Criar o frame de mídia do WordPress
    var frame;
    
    // Função para atualizar a visualização da imagem
    function updateImagePreview(attachment) {
        if (!attachment) {
            $imageContainer.empty();
            return;
        }
        
        // Usar o tamanho thumbnail para preview
        var imageUrl = attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url;
        var img = $('<img/>', {
            src: imageUrl,
            alt: attachment.alt,
            style: 'max-width: 100%;'
        });
        
        $imageContainer.html(img);
    }
    
    $uploadButton.on('click', function(e) {
        e.preventDefault();
        
        // Se o frame já existe, abra-o
        if (frame) {
            frame.open();
            return;
        }
        
        // Crie um novo frame de mídia
        frame = wp.media({
            title: 'Selecione ou Faça Upload de uma Imagem',
            button: {
                text: 'Usar esta imagem'
            },
            multiple: false
        });
        
        // Quando uma imagem for selecionada
        frame.on('select', function() {
            var attachment = frame.state().get('selection').first().toJSON();
            
            // Atualiza o preview da imagem
            updateImagePreview(attachment);
            
            // Atualiza o input hidden com o ID da imagem
            $imageInput.val(attachment.id);
            
            // Mostra o botão de remover
            $removeButton.show();
        });
        
        frame.open();
    });
    
    // Remover imagem
    $removeButton.on('click', function(e) {
        e.preventDefault();
        
        // Limpa o preview
        updateImagePreview(null);
        
        // Limpa o input hidden
        $imageInput.val('');
        
        // Esconde o botão de remover
        $(this).hide();
    });
    
    // Carregar preview inicial se existir uma imagem
    var initialImageId = $imageInput.val();
    if (initialImageId) {
        wp.media.attachment(initialImageId).fetch().then(function() {
            var attachment = wp.media.attachment(initialImageId);
            updateImagePreview(attachment.attributes);
            $removeButton.show();
        });
    }
}); 