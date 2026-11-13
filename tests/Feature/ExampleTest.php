<?php

test('the root url redirects to home', function () {
    $this->get('/')->assertRedirect('/home');
});
