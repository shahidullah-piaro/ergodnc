<?php
namespace App\Repositories;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Models\User;

class UserRepository implements UserRepositoryInterface{

    public function all(){
        return User::query()->orderBy('id', 'asc')->paginate(10);
    }

    public function store($data){
        return User::create($data);
    }

    public function show($id){
        return User::find($id);
    }

    public function update($data, $id){
        $user = User::find($id);
        return $user->update($data);
    }

    public function delete($id){
        $user = User::find($id);
        $user->delete();
    }

    // public function findByFilters($filter, $filterValue){        
    //     return User::query()->orderBy('id', 'asc')->where($filter, 'LIKE', '%%' . $filterValue . '%%')->paginate(5);
    // }

}