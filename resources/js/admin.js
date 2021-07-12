require('./bootstrap');

//confirmation request for every single restaurant
const delForm = document.querySelectorAll('.delete');

delForm.forEach( form=>{
    form.addEventListener('submit', function(event) {
        const resp = confirm('Do you really want to delete this post?');

        if(!resp) {
            event.preventDefault();
        }
    });
});