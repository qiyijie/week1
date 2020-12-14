<?php

namespace app\home\controller;

use app\home\model\article;
use Qiniu\Auth;
use Qiniu\Storage\UploadManager;
use think\Controller;
use think\Request;

class Index extends Controller
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $title = input('title');
        //查询数据
        $data = article::whereOr('title','like',"%{$title}%")->paginate(10,false,['query'=>['title'=>$title]]);
      return view('index',['data'=>$data]);

    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        //展示表单提交页面
        return view('create');
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        //接受数据
        $param  = $request->param();
        // 验证标题必填，并且最多50个字符
       $result = $this->validate($param,[
          'title|文章标题' => 'require|max:50'
       ]);
       if (true !== $result){
           $this->error($result);
       }

        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file('image');
        // 移动到框架应用根目录/public/uploads/ 目录下
        $info = $file->validate(['size'=>88888888,'ext'=>'jpg,png,gif'])->move(ROOT_PATH . 'public' . DS . 'uploads');
        if($info){

            // 输出 20160820/42a79759f284b767dfcb2a0197904287.jpg

            $param['image'] =  $info->getSaveName();
            $param['is_show'] = '0';
            $data = article::create($param,true);
            if ($data){
                cache('info',$param);
                $this->success('添加成功','index');
            }else{
                $this->error('添加失败');
            }
        }else{
            // 上传失败获取错误信息
          $this->error($file->getError());
        }

    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        //
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        if (!is_numeric($id)){
            $this->error('没有这条数据');
        }
        $data = article::where('id',$id)->find()->toArray();
        if ($data['is_show'] == 1){
            $this->error('正在显示不能删除');
        }
       if (article::destroy($id)){
           $this->success('删除成功','index');
       }
    }
}
