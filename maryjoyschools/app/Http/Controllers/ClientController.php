<?php

namespace App\Http\Controllers;

use App\Http\Requests\StudentRequest;
use App\Http\Resources\StudentResource;
use App\Services\StudentService;
use App\Models\Client;
use App\Models\PaymentMethod;
use App\Models\EcommerceClient;
use App\Models\Setting;
use App\Models\Account;
use App\utils\helpers;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use App\Models\SaleReturn;
use App\Models\PaymentSaleReturns;
use App\Models\Sale;
use App\Models\PaymentSale;
use DB;
use Illuminate\Http\Request;

class ClientController extends BaseController
{
    protected $studentService;

    public function __construct(StudentService $studentService)
    {
        $this->studentService = $studentService;
    }

    //------------- Get ALL Customers -------------\\

    public function index(request $request)
    {
        $this->authorizeForUser($request->user('api'), 'view', Client::class);
        // How many items do you want to display.
        $perPage = $request->limit;
        $pageStart = \Request::get('page', 1);
        // Start displaying items from this number;
        $offSet = ($pageStart * $perPage) - $perPage;
        $order = $request->SortField;
        $dir = $request->SortType;
        $helpers = new helpers();
        // Filter fields With Params to retrieve - name and class_grade for students
        $columns = array(0 => 'name', 1 => 'class_grade');
        $param = array(0 => 'like', 1 => '=');
        $data = array();
        $clients = Client::where('deleted_at', '=', null);

        //Multiple Filter
        $Filtred = $helpers->filter($clients, $columns, $param, $request)
        // Search With Multiple Param
            ->where(function ($query) use ($request) {
                return $query->when($request->filled('search'), function ($query) use ($request) {
                    return $query->where('name', 'LIKE', "%{$request->search}%");
                });
            })
            // Filter by class/grade
            ->when($request->filled('class_grade'), function ($query) use ($request) {
                return $query->where('class_grade', $request->class_grade);
            });
        $totalRows = $Filtred->count();
        if($perPage == "-1"){
            $perPage = $totalRows;
        }
        $clients = $Filtred->offset($offSet)
            ->limit($perPage)
            ->orderBy($order, $dir)
            ->get();

        foreach ($clients as $client) {

            $client_exist = EcommerceClient::where('client_id', $client->id)->exists();

            if($client_exist){
                $item['client_ecommerce'] = 'yes';
            }else{
                $item['client_ecommerce'] = 'no';
            }

            $item['total_amount'] = DB::table('sales')
                ->where('deleted_at', '=', null)
                ->where('statut', 'completed')
                ->where('client_id', $client->id)
                ->sum('GrandTotal');

            $item['total_paid'] = DB::table('sales')
                ->where('deleted_at', '=', null)
                ->where('statut', 'completed')
                ->where('client_id', $client->id)
                ->sum('paid_amount');

            $item['due'] = $item['total_amount'] - $item['total_paid'];

            $item['total_amount_return'] = DB::table('sale_returns')
                ->where('deleted_at', '=', null)
                ->where('client_id', $client->id)
                ->sum('GrandTotal');

            $item['total_paid_return'] = DB::table('sale_returns')
                ->where('sale_returns.deleted_at', '=', null)
                ->where('sale_returns.client_id', $client->id)
                ->sum('paid_amount');

            $item['return_Due'] = $item['total_amount_return'] - $item['total_paid_return'];

            $item['id'] = $client->id;
            $item['name'] = $client->name;
            $item['phone'] = $client->phone;
            $item['tax_number'] = $client->tax_number;
            $item['code'] = $client->code;
            $item['email'] = $client->email;
            $item['country'] = $client->country;
            $item['city'] = $client->city;
            $item['adresse'] = $client->adresse;
            
            // Student-specific fields
            $item['student_id'] = $client->student_id;
            $item['admission_number'] = $client->admission_number;
            $item['class_grade'] = $client->class_grade;
            $item['stream'] = $client->stream;
            $item['house'] = $client->house;
            $item['date_of_birth'] = $client->date_of_birth;
            $item['gender'] = $client->gender;
            $item['parent_guardian_name'] = $client->parent_guardian_name;
            $item['parent_guardian_phone'] = $client->parent_guardian_phone;
            $item['parent_guardian_email'] = $client->parent_guardian_email;
            $item['emergency_contact'] = $client->emergency_contact;
            $item['account_balance'] = $client->account_balance;
            $item['credit_limit'] = $client->credit_limit;
            $item['available_credit'] = $client->available_credit;
            $item['status'] = $client->status;
            $item['notes'] = $client->notes;
            $item['age'] = $client->age;
            $item['full_student_info'] = $client->full_student_info;
            
            $data[] = $item;
        }

        $company_info = Setting::where('deleted_at', '=', null)->first();
        $accounts = Account::where('deleted_at', '=', null)->orderBy('id', 'desc')->get(['id','account_name']);

        $clientsWithoutEcommerce = \App\Models\Client::whereNotIn('id', function($query){
            $query->select('client_id')->from('ecommerce_clients');
        })->count();

        $module_name = config('store.name');
        
        return response()->json([
            'clients' => $data,
            'company_info' => $company_info,
            'totalRows' => $totalRows,
            'clients_without_ecommerce' => $clientsWithoutEcommerce,
            'module_name' => $module_name,
            'accounts' => $accounts,
        ]);
    }

    //------------- Store new Student -------------\\

    public function store(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'create', Client::class);

        $this->validate($request, [
            'name' => 'required',
            'student_id' => 'nullable|unique:clients,student_id',
            'admission_number' => 'nullable|unique:clients,admission_number',
            'email' => 'nullable|email',
            'parent_guardian_email' => 'nullable|email',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:Male,Female',
            'status' => 'nullable|in:Active,Inactive,Graduated,Transferred',
            'account_balance' => 'nullable|numeric|min:0',
            'credit_limit' => 'nullable|numeric|min:0',
        ]);

        // Convert empty strings to null for nullable fields
        $request->merge([
            'email' => $request->email ?: null,
            'parent_guardian_email' => $request->parent_guardian_email ?: null,
            'student_id' => $request->student_id ?: null,
            'admission_number' => $request->admission_number ?: null,
            'class_grade' => $request->class_grade ?: null,
            'stream' => $request->stream ?: null,
            'house' => $request->house ?: null,
            'date_of_birth' => $request->date_of_birth ?: null,
            'gender' => $request->gender ?: null,
            'parent_guardian_name' => $request->parent_guardian_name ?: null,
            'parent_guardian_phone' => $request->parent_guardian_phone ?: null,
            'emergency_contact' => $request->emergency_contact ?: null,
            'notes' => $request->notes ?: null,
            'tax_number' => $request->tax_number ?: null,
            'adresse' => $request->adresse ?: null,
            'city' => $request->city ?: null,
            'phone' => $request->phone ?: null,
        ]);

        Client::create([
            'name' => $request['name'],
            'code' => $this->getNumberOrder(),
            'adresse' => $request['adresse'],
            'phone' => $request['phone'],
            'email' => $request['email'],
            'country' => $request['country'] ?? 'Kenya', // Default to Kenya
            'city' => $request['city'],
            'tax_number' => $request['tax_number'],
            // Student-specific fields
            'student_id' => $request['student_id'],
            'admission_number' => $request['admission_number'],
            'class_grade' => $request['class_grade'],
            'stream' => $request['stream'],
            'house' => $request['house'],
            'date_of_birth' => $request['date_of_birth'],
            'gender' => $request['gender'],
            'parent_guardian_name' => $request['parent_guardian_name'],
            'parent_guardian_phone' => $request['parent_guardian_phone'],
            'parent_guardian_email' => $request['parent_guardian_email'],
            'emergency_contact' => $request['emergency_contact'],
            'account_balance' => $request['account_balance'] ?? 0.00,
            'credit_limit' => $request['credit_limit'] ?? 0.00,
            'status' => $request['status'] ?? 'Active',
            'notes' => $request['notes'],
        ]);
        return response()->json(['success' => true]);
    }

    //------------ function show -----------\\

    public function show($id){
        $this->authorizeForUser(request()->user('api'), 'view', Client::class);
        
        $client = Client::findOrFail($id);
        
        // If this is a student, include additional student-specific data
        if ($client->student_id) {
            $client->load(['sales' => function($query) {
                $query->latest()->limit(10);
            }]);
            
            // Calculate student statistics
            $client->total_purchases = $client->sales()->sum('GrandTotal');
            $client->pending_balance = $client->sales()->where('payment_statut', '!=', 'paid')->sum('GrandTotal') - 
                                     $client->sales()->where('payment_statut', '!=', 'paid')->sum('paid_amount');
        }
        
        return response()->json($client);
    }

    //------------- Update Student -------------\\

    public function update(Request $request, $id)
    {
        $this->authorizeForUser($request->user('api'), 'update', Client::class);
        
        $this->validate($request, [
            'name' => 'required',
            'student_id' => 'nullable|unique:clients,student_id,' . $id,
            'admission_number' => 'nullable|unique:clients,admission_number,' . $id,
            'email' => 'nullable|email',
            'parent_guardian_email' => 'nullable|email',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:Male,Female',
            'status' => 'nullable|in:Active,Inactive,Graduated,Transferred',
            'account_balance' => 'nullable|numeric|min:0',
            'credit_limit' => 'nullable|numeric|min:0',
        ]);

        // Convert empty strings to null for nullable fields
        $request->merge([
            'email' => $request->email ?: null,
            'parent_guardian_email' => $request->parent_guardian_email ?: null,
            'student_id' => $request->student_id ?: null,
            'admission_number' => $request->admission_number ?: null,
            'class_grade' => $request->class_grade ?: null,
            'stream' => $request->stream ?: null,
            'house' => $request->house ?: null,
            'date_of_birth' => $request->date_of_birth ?: null,
            'gender' => $request->gender ?: null,
            'parent_guardian_name' => $request->parent_guardian_name ?: null,
            'parent_guardian_phone' => $request->parent_guardian_phone ?: null,
            'emergency_contact' => $request->emergency_contact ?: null,
            'notes' => $request->notes ?: null,
            'tax_number' => $request->tax_number ?: null,
            'adresse' => $request->adresse ?: null,
            'city' => $request->city ?: null,
            'phone' => $request->phone ?: null,
        ]);

        Client::whereId($id)->update([
            'name' => $request['name'],
            'adresse' => $request['adresse'],
            'phone' => $request['phone'],
            'email' => $request['email'],
            'country' => $request['country'],
            'city' => $request['city'],
            'tax_number' => $request['tax_number'],
            // Student-specific fields
            'student_id' => $request['student_id'],
            'admission_number' => $request['admission_number'],
            'class_grade' => $request['class_grade'],
            'stream' => $request['stream'],
            'house' => $request['house'],
            'date_of_birth' => $request['date_of_birth'],
            'gender' => $request['gender'],
            'parent_guardian_name' => $request['parent_guardian_name'],
            'parent_guardian_phone' => $request['parent_guardian_phone'],
            'parent_guardian_email' => $request['parent_guardian_email'],
            'emergency_contact' => $request['emergency_contact'],
            'account_balance' => $request['account_balance'],
            'credit_limit' => $request['credit_limit'],
            'status' => $request['status'],
            'notes' => $request['notes'],
        ]);
        return response()->json(['success' => true]);

    }

    //------------- delete client -------------\\

    public function destroy(Request $request, $id)
    {
        $this->authorizeForUser($request->user('api'), 'delete', Client::class);

        Client::whereId($id)->update([
            'deleted_at' => Carbon::now(),
        ]);
        return response()->json(['success' => true]);
    }

    //-------------- Delete by selection  ---------------\\

    public function delete_by_selection(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'delete', Client::class);
        $selectedIds = $request->selectedIds;

        foreach ($selectedIds as $Client_id) {
            Client::whereId($Client_id)->update([
                'deleted_at' => Carbon::now(),
            ]);
        }
        return response()->json(['success' => true]);
    }


    //------------- get Number Order Customer -------------\\

    public function getNumberOrder()
    {
        $last = DB::table('clients')->latest('id')->first();

        if ($last) {
            $code = $last->code + 1;
        } else {
            $code = 1;
        }
        return $code;
    }

    //------------- Get Clients Without Paginate -------------\\

    public function Get_Clients_Without_Paginate()
    {
        $clients = Client::where('deleted_at', '=', null)->get(['id', 'name']);
        return response()->json($clients);
    }

    //------------- Get Clients get_client_store_data Paginate -------------\\

    public function get_client_store_data($id)
    {
        $client = EcommerceClient::where('client_id', $id)->first();

        $data['id']        = $client->id;
        $data['client_id'] = $client->client_id;
        $item['name']      = $client->username;
        $data['email']     = $client->email;
        $data['NewPassword']     = NULL;

        return response()->json($data);
    }



     // import clients
     public function import_clients(Request $request)
     {
 
         ini_set('max_execution_time', 600); //600 seconds = 10 minutes 
        
         $file = $request->file('clients');
         $ext = pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);
         if ($ext != 'csv') {
             return response()->json([
                 'msg' => 'must be in csv format',
                 'status' => false,
             ]);
         } else {
             $data = [];
             $rowcount = 0;
             if (($handle = fopen($file->getPathname(), "r")) !== false) {
                 $max_line_length = defined('MAX_LINE_LENGTH') ? MAX_LINE_LENGTH : 10000;
                 $header = fgetcsv($handle, $max_line_length, ';'); // Use semicolon as the delimiter
             
                 // Process the header row
                 $escapedHeader = [];
                 foreach ($header as $key => $value) {
                     $lheader = strtolower($value);
                     $escapedItem = preg_replace('/[^a-z]/', '', $lheader);
                     $escapedHeader[] = $escapedItem;
                 }
             
                 $header_colcount = count($header);
                 while (($row = fgetcsv($handle, $max_line_length, ';')) !== false) { // Use semicolon as the delimiter
                     $row_colcount = count($row);
                     if ($row_colcount == $header_colcount) {
                         // Replace empty values with null
                         $row = array_map(function($value) {
                             return $value === '' ? null : $value;
                         }, $row);
                         
                         $entry = array_combine($escapedHeader, $row);
                         $data[] = $entry;
                     } else {
                         return null;
                     }
                     $rowcount++;
                 }
                 fclose($handle);
             } else {
                 return null;
             }
             
 
             $cleanedData = [];
 
             foreach ($data as $row) {
                 $cleanedRow = [];
                 foreach ($row as $key => $value) {
                     $cleanedKey = trim($key);
                     $cleanedRow[$cleanedKey] = $value;
                 }
                 $cleanedData[] = $cleanedRow;
             }
         
            
             $rules = array(
                 'name' => 'required',
                 'studentid' => 'nullable|unique:clients,student_id',
                 'admissionnumber' => 'nullable|unique:clients,admission_number',
                 'email' => 'nullable|email',
                 'phone' => 'nullable|regex:/^\+?[0-9\s\-\(\)]+$/',
                 'dateofbirth' => 'nullable|date|before:today',
                 'accountbalance' => 'nullable|numeric|min:0',
                 'creditlimit' => 'nullable|numeric|min:0'
             );
 
             //-- Create New Client
             foreach ($cleanedData as $key => $value) {
                 $input = [
                     'name' => $value['name'],
                     'studentid' => $value['studentid'] ?? null,
                     'admissionnumber' => $value['admissionnumber'] ?? null,
                     'email' => $value['email'] ?? null,
                     'phone' => $value['phone'] ?? null,
                     'dateofbirth' => $value['dateofbirth'] ?? null,
                     'accountbalance' => $value['accountbalance'] ?? 0,
                     'creditlimit' => $value['creditlimit'] ?? 0
                 ];
 
                 $validator = Validator::make($input, $rules);
                 if (!$validator->fails()) {
                     
                     Client::create([
                         'name' => $value['name'],
                         'code' => $this->getNumberOrder(),
                         'adresse' => $value['adresse'] ?? null,
                         'phone' => $value['phone'] ?? null,
                         'email' => $value['email'] ?? null,
                         'country' => $value['country'] ?? 'Kenya',
                         'city' => $value['city'] ?? null,
                         'tax_number' => $value['taxnumber'] ?? null,
                         // Student-specific fields
                         'student_id' => $value['studentid'] ?? null,
                         'admission_number' => $value['admissionnumber'] ?? null,
                         'class_grade' => $value['classgrade'] ?? null,
                         'stream' => $value['stream'] ?? null,
                         'house' => $value['house'] ?? null,
                         'date_of_birth' => $value['dateofbirth'] ?? null,
                         'gender' => $value['gender'] ?? null,
                         'parent_guardian_name' => $value['parentguardianname'] ?? null,
                         'parent_guardian_phone' => $value['parentguardianphone'] ?? null,
                         'parent_guardian_email' => $value['parentguardianemail'] ?? null,
                         'emergency_contact' => $value['emergencycontact'] ?? null,
                         'account_balance' => $value['accountbalance'] ?? 0.00,
                         'credit_limit' => $value['creditlimit'] ?? 0.00,
                         'status' => $value['status'] ?? 'Active',
                         'notes' => $value['notes'] ?? null,
                     ]);
 
                 }
                
 
             }
 
             return response()->json([
                 'status' => true,
             ], 200);
         }
 
     }
 


     //------------- clients_pay_due -------------\\

     public function clients_pay_due(Request $request)
     {
         $this->authorizeForUser($request->user('api'), 'pay_due', Client::class);
        
         if($request['amount'] > 0){
            $client_sales_due = Sale::where('deleted_at', '=', null) 
            ->where('statut', 'completed')
            ->where([
                ['payment_statut', '!=', 'paid'],
                ['client_id', $request->client_id]
            ])->get();

            $paid_amount_total = $request->amount;

            foreach($client_sales_due as $key => $client_sale){
                if($paid_amount_total == 0)
                break;
                $due = $client_sale->GrandTotal  - $client_sale->paid_amount;

                if($paid_amount_total >= $due){
                    $amount = $due;
                    $payment_status = 'paid';
                }else{
                    $amount = $paid_amount_total;
                    $payment_status = 'partial';
                }

                $payment_sale = new PaymentSale();
                $payment_sale->sale_id = $client_sale->id;
                $payment_sale->account_id =  $request['account_id']?$request['account_id']:NULL;
                $payment_sale->Ref = app('App\Http\Controllers\PaymentSalesController')->getNumberOrder();
                $payment_sale->date = Carbon::now();
                $payment_sale->payment_method_id = $request['payment_method_id'];
                $payment_sale->montant = $amount;
                $payment_sale->change = 0;
                $payment_sale->notes = $request['notes'];
                $payment_sale->user_id = Auth::user()->id;
                $payment_sale->save();

                $account = Account::where('id', $request['account_id'])->exists();

                if ($account) {
                    // Account exists, perform the update
                    $account = Account::find($request['account_id']);
                    $account->update([
                        'balance' => $account->balance + $amount,
                    ]);
                }

                $client_sale->paid_amount += $amount;
                $client_sale->payment_statut = $payment_status;
                $client_sale->save();

                $paid_amount_total -= $amount;
            }
        }
        
         return response()->json(['success' => true]);
 
     }

    //------------- clients_pay_sale_return_due -------------\\

    public function pay_sale_return_due(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'pay_sale_return_due', Client::class);
        
        if($request['amount'] > 0){
            $client_sell_return_due = SaleReturn::where('deleted_at', '=', null)
            ->where([
                ['payment_statut', '!=', 'paid'],
                ['client_id', $request->client_id]
            ])->get();

            $paid_amount_total = $request->amount;

            foreach($client_sell_return_due as $key => $client_sale_return){
                if($paid_amount_total == 0)
                break;
                $due = $client_sale_return->GrandTotal  - $client_sale_return->paid_amount;

                if($paid_amount_total >= $due){
                    $amount = $due;
                    $payment_status = 'paid';
                }else{
                    $amount = $paid_amount_total;
                    $payment_status = 'partial';
                }

                $payment_sale_return = new PaymentSaleReturns();
                $payment_sale_return->sale_return_id = $client_sale_return->id;
                $payment_sale_return->account_id =  $request['account_id']?$request['account_id']:NULL;
                $payment_sale_return->Ref = app('App\Http\Controllers\PaymentSaleReturnsController')->getNumberOrder();
                $payment_sale_return->date = Carbon::now();
                $payment_sale_return->payment_method_id = $request['payment_method_id'];
                $payment_sale_return->montant = $amount;
                $payment_sale_return->change = 0;
                $payment_sale_return->notes = $request['notes'];
                $payment_sale_return->user_id = Auth::user()->id;
                $payment_sale_return->save();

                $account = Account::where('id', $request['account_id'])->exists();

                if ($account) {
                    // Account exists, perform the update
                    $account = Account::find($request['account_id']);
                    $account->update([
                        'balance' => $account->balance - $amount,
                    ]);
                }

                $client_sale_return->paid_amount += $amount;
                $client_sale_return->payment_statut = $payment_status;
                $client_sale_return->save();

                $paid_amount_total -= $amount;
            }
        }
        
        return response()->json(['success' => true]);

    }

    //------------- Get Students by Class -------------\\
    public function getStudentsByClass(Request $request, $class)
    {
        $this->authorizeForUser($request->user('api'), 'view', Client::class);
        
        $students = Client::where('deleted_at', '=', null)
            ->where('class_grade', $class)
            ->where('status', 'Active')
            ->orderBy('name')
            ->get(['id', 'name', 'student_id', 'admission_number', 'stream', 'account_balance', 'credit_limit']);
            
        return response()->json($students);
    }

    //------------- Update Student Account Balance -------------\\
    public function updateAccountBalance(Request $request, $id)
    {
        $this->authorizeForUser($request->user('api'), 'update', Client::class);
        
        $this->validate($request, [
            'amount' => 'required|numeric',
            'type' => 'required|in:credit,debit',
            'description' => 'nullable|string'
        ]);

        $student = Client::findOrFail($id);
        
        if (!$student->student_id) {
            return response()->json(['error' => 'This client is not a student'], 400);
        }

        $success = $this->studentService->updateAccountBalance(
            $student, 
            $request->amount, 
            $request->type, 
            $request->description
        );

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'Account balance updated successfully',
                'new_balance' => $student->fresh()->account_balance,
                'available_credit' => $student->fresh()->available_credit
            ]);
        } else {
            return response()->json(['error' => 'Failed to update account balance'], 500);
        }
    }

    //------------- Check Student Purchase Eligibility -------------\\
    public function checkPurchaseEligibility(Request $request, $id)
    {
        $this->authorizeForUser($request->user('api'), 'view', Client::class);
        
        $this->validate($request, [
            'amount' => 'required|numeric|min:0'
        ]);

        $client = Client::findOrFail($id);
        $amount = $request->amount;
        
        $canPurchase = $client->canPurchase($amount);
        
        return response()->json([
            'can_purchase' => $canPurchase,
            'account_balance' => $client->account_balance,
            'credit_limit' => $client->credit_limit,
            'available_credit' => $client->available_credit,
            'required_amount' => $amount,
            'shortfall' => $canPurchase ? 0 : ($amount - $client->available_credit)
        ]);
    }

    //------------- Get Student Statistics -------------\\
    public function getStudentStatistics(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'view', Client::class);
        
        $stats = [
            'total_students' => Client::where('deleted_at', '=', null)->count(),
            'active_students' => Client::where('deleted_at', '=', null)->where('status', 'Active')->count(),
            'inactive_students' => Client::where('deleted_at', '=', null)->where('status', 'Inactive')->count(),
            'graduated_students' => Client::where('deleted_at', '=', null)->where('status', 'Graduated')->count(),
            'transferred_students' => Client::where('deleted_at', '=', null)->where('status', 'Transferred')->count(),
            'total_account_balance' => Client::where('deleted_at', '=', null)->sum('account_balance'),
            'total_credit_limit' => Client::where('deleted_at', '=', null)->sum('credit_limit'),
        ];
        
        // Students by class
        $studentsByClass = Client::where('deleted_at', '=', null)
            ->where('status', 'Active')
            ->selectRaw('class_grade, COUNT(*) as count')
            ->groupBy('class_grade')
            ->orderBy('class_grade')
            ->get();
            
        $stats['students_by_class'] = $studentsByClass;
        
        return response()->json($stats);
    }

    //------------- Bulk Update Student Status -------------\\
    public function bulkUpdateStatus(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'update', Client::class);
        
        $this->validate($request, [
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:clients,id',
            'status' => 'required|in:Active,Inactive,Graduated,Transferred'
        ]);

        Client::whereIn('id', $request->student_ids)
            ->update(['status' => $request->status]);
            
        return response()->json(['success' => true]);
    }

    //------------- Get Students with Low Balance -------------\\
    public function getStudentsWithLowBalance(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'view', Client::class);
        
        $students = $this->studentService->getStudentsWithLowBalance();
        
        return StudentResource::collection($students);
    }

    //------------- Get Students with Negative Balance -------------\\
    public function getStudentsWithNegativeBalance(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'view', Client::class);
        
        $students = $this->studentService->getStudentsWithNegativeBalance();
        
        return StudentResource::collection($students);
    }

    //------------- Generate Student ID -------------\\
    public function generateStudentId(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'create', Client::class);
        
        $studentId = $this->studentService->generateStudentId();
        $admissionNumber = $this->studentService->generateAdmissionNumber();
        
        return response()->json([
            'student_id' => $studentId,
            'admission_number' => $admissionNumber
        ]);
    }

    //------------- Get Student Purchase History -------------\\
    public function getStudentPurchaseHistory(Request $request, $id)
    {
        $this->authorizeForUser($request->user('api'), 'view', Client::class);
        
        $student = Client::findOrFail($id);
        
        if (!$student->student_id) {
            return response()->json(['error' => 'This client is not a student'], 400);
        }

        $sales = $student->sales()
            ->with(['saleDetails.product', 'paymentSales'])
            ->orderBy('date', 'desc')
            ->paginate(20);

        return response()->json($sales);
    }

    //------------- Export Students to CSV -------------\\
    public function exportStudents(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'view', Client::class);
        
        $students = Client::whereNotNull('student_id')
            ->when($request->class_grade, function($query, $class) {
                return $query->where('class_grade', $class);
            })
            ->when($request->status, function($query, $status) {
                return $query->where('status', $status);
            })
            ->orderBy('class_grade')
            ->orderBy('stream')
            ->orderBy('name')
            ->get();

        $filename = 'students_export_' . date('Y-m-d_H-i-s') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($students) {
            $file = fopen('php://output', 'w');
            
            // CSV Headers
            fputcsv($file, [
                'Name', 'Student ID', 'Admission Number', 'Class', 'Stream', 'House',
                'Date of Birth', 'Gender', 'Phone', 'Email', 'Parent/Guardian Name',
                'Parent/Guardian Phone', 'Parent/Guardian Email', 'Account Balance',
                'Credit Limit', 'Status', 'Notes'
            ]);

            foreach ($students as $student) {
                fputcsv($file, [
                    $student->name,
                    $student->student_id,
                    $student->admission_number,
                    $student->class_grade,
                    $student->stream,
                    $student->house,
                    $student->date_of_birth ? $student->date_of_birth->format('Y-m-d') : '',
                    $student->gender,
                    $student->phone,
                    $student->email,
                    $student->parent_guardian_name,
                    $student->parent_guardian_phone,
                    $student->parent_guardian_email,
                    $student->account_balance,
                    $student->credit_limit,
                    $student->status,
                    $student->notes
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}