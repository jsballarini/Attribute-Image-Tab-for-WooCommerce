jQuery(document).ready(function($) {
    // Variáveis para os elementos do DOM
    var $uploadButton = $('.upload_image_button');
    var $removeButton = $('.remove_image_button');
    var $imagePreview = $('.image-preview');
    var $imageInput = $('.custom-tab-image-id');
    
    // Criar o frame de mídia do WordPress
    var frame;
    
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
            $imagePreview.attr('src', attachment.url).show();
            
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
        $imagePreview.attr('src', '').hide();
        
        // Limpa o input hidden
        $imageInput.val('');
        
        // Esconde o botão de remover
        $(this).hide();
    });
    
    // Mostrar botão de remover se já existe uma imagem
    if ($imageInput.val()) {
        $removeButton.show();
    }
}); 