<?php

use Illuminate\Database\Seeder;
use App\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UsersTableSeeder::class);
        $user = new User();
        $user->email = 'test@gmail.com';
        $user->password = bcrypt('123456');
        $user->channelId = '1476076743';
        $user->channelSecret = 'c3b5f65446faefcf1471609353cc943c';
        $user->channelMid = 'uaa357d613605ebf36f6366a7ce896180';
        $user->client_id = '1477592731';
        $user->client_secret = '789ac444af36a5020a5b4c74a9455f5f';
        $user->save();
    }
}
