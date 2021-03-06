<?php namespace App\Http\Controllers;

use App\Candidate;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;

class CandidateController extends Controller
{
    public function __construct()
    {
        //學生會限定
        $this->middleware('sa');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $candidateList = new Collection();
        //科系（依出現順序）
        $departmentArray = Candidate::orderBy('id', 'asc')->distinct('department')->select('department')->get()->toArray();
        $departmentList = [];
        foreach ($departmentArray as $department) {
            $departmentList[] = $department['department'];
        }
        foreach ($departmentList as &$value) {
            $value = "'$value'";
        }
        $departments = implode(',', $departmentList);
        //類型（依設定順序）
        foreach (Config::get('vote.type') as $voteType) {
            //取得候選人清單
            if ($voteType == '學生會會長') {
                $candidateTemp = Candidate::where('type', '=', $voteType)
                    ->orderBy('number', 'asc')
                    ->get();
            } else {
                $candidateTemp = Candidate::where('type', '=', $voteType)
                    ->orderByRaw(DB::raw("FIELD(department, " . $departments . ")"))
                    ->orderBy('number', 'asc')
                    ->get();
            }
            $candidateList = $candidateList->merge($candidateTemp);
        }
        return view('candidate.list')->with('candidateList', $candidateList);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        return view('candidate.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),
            array(
                'number' => 'required|integer|min:0',
                'job' => 'max:20',
                'name' => 'max:20',
                'department' => 'max:20',
                'class' => 'max:20',
                'type' => 'max:20'
            )
        );

        if ($validator->fails()) {
            return Redirect::route('candidate.create')
                ->withErrors($validator)
                ->withInput();
        } else {
            $candidate = Candidate::create(array(
                'number' => $request->get('number'),
                'job' => $request->get('job'),
                'name' => $request->get('name'),
                'department' => $request->get('department'),
                'class' => $request->get('class'),
                'type' => $request->get('type')
            ));

            Log::info('[Vote] ' . Auth::user()->id . ' ' . Auth::user()->name . ' 新增了候選人：' . $candidate->name, [
                'id' => $candidate->id,
                'number' => $candidate->number,
                'job' => $candidate->job,
                'name' => $candidate->name,
                'department' => $candidate->department,
                'class' => $candidate->class,
                'type' => $candidate->type
            ]);

            return Redirect::route('candidate.show', $candidate->id)
                ->with('global', '候選人資料已更新');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function show($id)
    {
        $user = Auth::user();
        $candidate = Candidate::find($id);
        if ($candidate) {
            return view('candidate.show')->with('user', $user)->with('candidate', $candidate);
        }
        return Redirect::route('candidate.index')
            ->with('warning', '候選人不存在');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function edit($id)
    {
        $candidate = Candidate::find($id);
        if ($candidate) {
            return view('candidate.edit')->with('candidate', $candidate);
        }
        return Redirect::route('candidate.index')
            ->with('warning', '候選人不存在');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        $candidate = Candidate::find($id);
        if (!$candidate) {
            return Redirect::route('candidate.index')
                ->with('warning', '候選人不存在');
        }

        $validator = Validator::make($request->all(),
            array(
                'number' => 'required|integer|min:0',
                'job' => 'max:20',
                'name' => 'max:20',
                'department' => 'max:20',
                'class' => 'max:20',
                'type' => 'max:20'
            )
        );

        if ($validator->fails()) {
            return Redirect::route('candidate.edit', $id)
                ->withErrors($validator)
                ->withInput();
        } else {
            $candidate->number = $request->get('number');
            $candidate->job = $request->get('job');
            $candidate->name = $request->get('name');
            $candidate->department = $request->get('department');
            $candidate->class = $request->get('class');
            $candidate->type = $request->get('type');
            $candidate->save();

            Log::info('[Vote] ' . Auth::user()->id . ' ' . Auth::user()->name . ' 更新了候選人：' . $candidate->name, [
                'id' => $candidate->id,
                'number' => $candidate->number,
                'job' => $candidate->job,
                'name' => $candidate->name,
                'department' => $candidate->department,
                'class' => $candidate->class,
                'type' => $candidate->type
            ]);

            return Redirect::route('candidate.show', $id)
                ->with('global', '候選人資料已更新');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return Response
     */
    public function destroy($id)
    {
        $candidate = Candidate::find($id);
        if ($candidate) {
            Log::info('[Vote] ' . Auth::user()->id . ' ' . Auth::user()->name . ' 刪除了候選人：' . $candidate->name, [
                'id' => $candidate->id,
                'number' => $candidate->number,
                'job' => $candidate->job,
                'name' => $candidate->name,
                'department' => $candidate->department,
                'class' => $candidate->class,
                'type' => $candidate->type
            ]);

            $candidate->delete();
            return Redirect::route('candidate.index')
                ->with('global', '候選人已刪除');
        }
        return Redirect::route('candidate.index')
            ->with('warning', '候選人不存在');
    }

}
