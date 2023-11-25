
import Routing from '../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js';


function ILike(id) {
    // alert(id) // this was to show message alert and confirm this is called
    var Route = Routing.generate('likes');
    // let Route = Routing.generate('likes')
    $.ajax({
        type: 'POST',
        url: Route,
        data: ({id:id}),
        async: true,
        dataType: 'json',
        success: function (data) {
            console.log(data['likes']);
            window.location.reload();
        }
    })
}