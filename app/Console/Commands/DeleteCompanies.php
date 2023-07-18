<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Position;
use App\Models\Employee;

class DeleteCompanies extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:delete-companies';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $thirtyDaysAgo = Carbon::now()->subDays(30)->format('Y-m-d');
        $companydetails = User::where('is_deleted', 1)
            ->where('deleted_at', '<', $thirtyDaysAgo)
            ->get();
        if(count($companydetails) > 0){
            foreach($companydetails as $company){
                $id = $company->id;
                DB::beginTransaction();
                $allEmployees = Employee::where('added_by', $id)->get(['id', 'profile_image']);

                // Delete the images from the database and file storage
                foreach ($allEmployees as $employee) {
                    try{
                        if ($employee->profile_image) {
                            // Check if the file exists before attempting to delete it
                            if(File::exists($employee->profile_image)){
                                File::delete($employee->profile_image);
                            }

                            // Update the image field in the database to null
                            Employee::where('id', $employee->id)->update(['profile_image' => null]);
                        }
                    }catch(\Exception $e){
                    }
                }
                // Delete all employees from the database
                Employee::where('added_by', $id)->delete();
                try{
                    if ($company->image) {
                        // Check if the file exists before attempting to delete it
                        if(File::exists($company->image)){
                            File::delete($company->image);
                        }

                        // Update the image field in the database to null
                        User::where('id', $id)->update(['image' => null]);
                    }
                }catch(\Exception $e){
                }
                $company->delete();
                Position::where("added_by", $id)->delete();
                DB::commit();
            }
            return true;
        }

        return true;
        
    }
}
