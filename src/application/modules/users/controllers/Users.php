<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Users extends MY_Controller
{
  function __construct()
  {
    parent::__construct();
  }

  public function index()
  {
    echo "users";
  }
}
