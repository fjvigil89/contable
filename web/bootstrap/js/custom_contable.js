/*$(document).ready(function()
	{
 		$("#mostrarmodal").modal("show");
});
 
function BuscarEmpleado(idEmpleado)   
{
    $('.titulo').html(idEmpleado);
    $("#mostrarmodal").modal("show");
}

function BuscarAreaResponsable(idArearesp)
{
    alert(idArearesp);
}
*/

 $(document).ready(function(){
  
        $(window).scroll(function(){
            if ($(this).scrollTop() > 100) {
                $('.scrollup').fadeIn();
            } else {
                $('.scrollup').fadeOut();
            }
        });
  
        $('.scrollup').click(function(){
            $("html, body").animate({ scrollTop: 0 }, 600);
            return false;
        });
  
    });