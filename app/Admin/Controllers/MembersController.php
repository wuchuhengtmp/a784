<?php

namespace App\Admin\Controllers;

use App\Models\Members;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Models\Members as Member;
use App\Models\MemberFollow;
use App\Models\Educations;
use App\Models\Accounts;
use App\Models\AccountLogs;
use App\Models\Levels as Level;
use App\Models\Region;
use App\Models\Favorites;
use App\Models\Images;
use App\Models\Comments;
use Encore\Admin\Widgets\Table;

class MembersController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '会员';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Member);
        $grid->disableCreateButton();
        //$grid->disableActions();
        $grid->filter(function($filter){
            // 在这里添加字段过滤器
            $filter->between('created_at', '创建时间')->datetime();
            $filter->like('nickname', '昵称')->placeholder('请输入昵称');
            $filter->equal('phone', '手机号码')->placeholder('请输入手机号码');
        });

        $grid->id('ID')->sortable();
        $grid->column('avatar.url', '头像')->display(function ($avatar) {
            $avatar =  '/uploads/' . $avatar;
            $el = <<< EOT
            <a href="{$avatar}" class="grid-popup-link"> <img src="{$avatar}" style="max-width:50px;max-height:50px" class="img img-thumbnail"> </a>
EOT;
            return $el;
        });
        $grid->nickname('昵称')->editable();
        $grid->sign('个性签名')->editable();
        $status = [
            'on'  => ['value' => 1, 'text' => '女', 'color' => 'warning'],
            'off' => ['value' => 2, 'text' => '男', 'color' => 'success'],
        ];
        $grid->column('sex', '性别')->switch($status);
        $grid->age('年龄')->sortable()->editable();
        $grid->born('生日')->display(function($date){
            return date('Y-m-d', strtotime($date));
        })->editable('date');
        $grid->region_id('所在地')->display(function($region_id){
            $region = Region::where('id', $region_id)->first();
            if ($region) return $region->MergerName;
        });
        $grid->phone('手机号码')->editable();
        $grid->email('邮箱')->editable();
        $grid->weixin('微信')->editable();
        $grid->column('job', '职业')->switch([
            'on'  => ['value' => 1, 'text' => '学生', 'color' => 'warning'],
            'off' => ['value' => 2, 'text' => '老师', 'color' => 'success'],
        ]
        );
        $grid->education_id('学历')->display(function($education_id){
            $result = Educations::where('id', $education_id)->first();
            return $result->name ?? null;
        });
        $grid->school('学校')->editable();
        $grid->department('院系')->editable();
        $grid->professional('专业')->editable();
        $grid->start_school_at('入学年份')->editable('date');
        $grid->next_plan('近期动向')->editable();
        $grid->hobby('兴趣爱好')->editable();
        $grid->column('becomment', '被评论')->display(function($model){
            return Comments::countCommentByMemberid($this->id);
        })->expand(function($model){
                $has_comments = Comments::getCommentsByMemberid($this->id);
                $data = [];
                if (count($has_comments)  >  0) {
                    foreach($has_comments as $el) {
                        $tmp['id'] = $el['id'];
                        $tmp['nickname'] = $el['name'];
                        $tmp['content'] = $el['content'];
                        $tmp['title'] = $el['title'];
                        $tmp['created_at'] = $el['created_at'];
                        $data[] = $tmp;
                    }
                }
                return new Table(['ID', '评论人', '内容', '被评论的资源', '时间'], $data); 
            });
        $grid->column('posts', '发布')
            ->display(function($posts){
                return count($posts);
            })
        ->expand(function($model){
            $Member = Members::where('id', $this->id)->with(['posts'=>function($query){
                $query->orderby('id', 'descc')->take(10)->select('*');
            }])->first();
              $data = [];
              if ($Member->posts) {
                  foreach($Member->posts as $el) {
                        $tmp['id']  = $el['id'];
                        $tmp['title'] = $el['title'];
                        $tmp['created_at'] = $el['created_at'];
                        $data[] = $tmp;
                  }
              } 
              return new Table(['ID', '标题', '发布时间'], $data);
        });
        $grid->column('follows', '关注')
            ->display(function(){
                $Data= Member::where('id', $this->id)->withCount('follows')->first();
                return $Data->follows_count;
            })
            ->expand(function($model){
                $hasData = Member::where('id', $this->id)->with('follows')->first();
                $data = []; 
                if (isset($hasData->follows) && $hasData->follows) {
                    foreach($hasData->follows as $el) {
                        $tmp['id'] = $el['id'];
                        $tmp['nickname'] = $el['nickname'];
                        $data[] = $tmp;
                    } 
                }
                return new Table(['ID', '昵称'], $data);
            });
        $grid->column('favorites', '收藏')
            ->display(function($model) {
                $hasData = Member::where('id', $this->id)->withCount('favorites')->first();
                return $hasData->favorites_count;
            })
            ->expand(function($model){
                $hasData = Member::where('id', $this->id)->withCount('favorites')->first();
                $data = []; 
                if (isset($hasData->favorites) && $hasData->follows) {
                    foreach($hasData->favorites as $el) {
                        $tmp['id'] = $el['id'];
                        $tmp['title'] = $el['title'];
                        $data[] = $tmp;
                    } 
                }
                return new Table(['ID', '标题'], $data);
            });
        $grid->balance('龙币');
        $grid->level('等级')->display(function(){
            $money = AccountLogs::getMaxBetweenTimeByUid($this->id,  time() - 60*60*24*365);
            $fans = MemberFollow::countFansBYUid($this->id);
            $has_level = Level::getLevelByFansAndMony($fans, $money);
            if ($has_level)
                return $has_level->name;
            else
                return "暂无等级";
        });

        $states = [
            'on'  => ['value' => 0, 'text' => '封号', 'color' => 'primary'],
            'off' => ['value' => 1, 'text' => '正常', 'color' => 'default'],
        ];
        $grid->column('status','是否禁用')->switch($states);


        $grid->created_at('创建时间');

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(Members::findOrFail($id));
        $show->panel()
            ->tools(function ($tools) {
                $tools->disableEdit();
                $tools->disableList();
                $tools->disableDelete();
            });
        $show->field('name', __('Name'));
        $show->avatar('头像')->as(function($avatar){
            return $avatar->url; 
        })->image();
        $show->field('email', '邮箱');
        $show->field('nickname', '昵称');
        $show->field('region_id', __('Region id'));
        $show->field('sign', '签名');
        $show->field('sex', '性别')->as(function($sex_id){
            return $sex_id == 1 ? '女' :  '男';
        });
        $show->field('age', '年龄');
        $show->field('born', '生日');
        $show->field('job', '职业')->as(function($job_id){
            return $job_id  == 1 ?  '学生' : '老师';
        });
        $show->field('weixin', '微信');
        $show->field('phone', '手机');
        $show->field('school', '毕业学校');
        $show->field('department', '院系');
        $show->field('professional', "专业");
        $show->field('education', '学历')->as(function($education){
            if (isset($education->name))
                return $education->name;
            else 
                return null;
        });
        $show->field('start_school_at', '入学时间');
        $show->field('hobby', '爱好');
        $show->field('next_plan', '近期动向');
        $show->field('status', '是否禁用')->as(function($status_id){
            return $status_id == 1 ? '正常' : '禁用';
        });
        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Members);
        $form->text('name', '姓名');
        $form->text('password', '密码');
        $form->image('avatar.url', '头像')->move('public/upload/image1');;
        $form->date('born', '生日');
        $form->text('next_plan', '近期动向');
        $form->date('start_school_at', '入学年份');
        $form->text('email', '邮件')->rules('email');
        $form->text('weixin', '微信号');
        $form->text('school', '毕业学校');
        $form->text('professional', '专业');
        $form->text('department', '院系');
        $form->text('age', '年龄')->rules('required|numeric');
        $form->text('phone', '手机')->rules('required|numeric');
        $form->switch('sex', '性别')->states([
            'on'  => ['value' => 1, 'text' => '女', 'color' => 'warning'],
            'off' => ['value' => 2, 'text' => '男', 'color' => 'success'],
        ]);
        $form->switch('job', '职业')->states([
            'on'  => ['value' => 1, 'text' => '学生', 'color' => 'warning'],
            'off' => ['value' => 2, 'text' => '老师', 'color' => 'success'],
        ]);
        $states = [
            'on'  => ['value' => 0, 'text' => '封号', 'color' => 'primary'],
            'off' => ['value' => 1, 'text' => '正常', 'color' => 'default'],
        ];
        $form->switch('status', '是否封号')->states($states);
        $form->saving(function (Form $form) {
            if ($form->password && $form->model()->password != $form->password) {
                $form->password = bcrypt($form->password);
            }
        });
        return $form;
    }
}
