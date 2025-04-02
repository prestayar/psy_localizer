$(document).ready(function(){
    $(".grower").click(function(){
        var element = $(this);
        if(element.hasClass('open')){
            element.addClass('close').removeClass('open');
            element.parent().find('ul:first').slideUp();
        }
        else{
            $('.grower').not(element).addClass('close').removeClass('open');
            var elementUL = element.parent().find('ul:first');
            $('.list-group-item ul').not(elementUL).slideUp();

            element.addClass('open').removeClass('close');
            element.parent().find('ul:first').slideDown();
        }
    });
});

function toggleMenu(arrow) {
    const menu = arrow.closest('.wsdk-menu');
    menu.classList.toggle('wsdk-menu-closed');    
    
    const info = document.querySelector('#wsdk-info');
    info.classList.toggle('wsdk-info-closed');
}