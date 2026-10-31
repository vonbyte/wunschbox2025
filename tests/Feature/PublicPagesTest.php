<?php

test('the wishbox and the wish note load for guests', function () {
    $this->get(route('wishbox'))->assertOk();
    $this->get(route('wishlist'))->assertOk();
});
