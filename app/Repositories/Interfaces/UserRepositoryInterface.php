<?php 
namespace App\Repositories\Interfaces;

interface UserRepositoryInterface
{

    public function all();

    public function store($data);

    public function show($id);

    public function update($data, $id);

    public function delete($id);

    //public function findByFilters($filter, $filterValue);

}