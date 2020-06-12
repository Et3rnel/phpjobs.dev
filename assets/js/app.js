import Vue from 'vue'

// any CSS you import will output into a single css file (app.css in this case)
import '../css/app.scss';

console.log('Hello Webpack Encore! Edit me in assets/js/app.js');

Vue.component('job-item', {
    template: '<div>This is a job</div>'
})

var job = new Vue({
    el: '#myjob',
})