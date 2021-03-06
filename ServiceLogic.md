##  角色、权限相关逻辑:

 -  对于项目而言，角色分为 **通用角色** 与 **项目自定义角色**。  
    **通用角色**：每个项目均存在且无法修改的角色，对应 *roles.project_id* 为空的角色。  
    **项目自定义角色**：项目自定义且可修改的角色，对应 *roles.project_id* 不为空的角色。  

 -  对于用户而言，角色分为 **项目角色** 与 **节点角色**。  
    **项目角色**：是用户在项目中的角色，对应字段为 *project_user.role_id*。  
    **节点角色**：是涉及节点逻辑的对应角色，对应字段为 *node_user.role_id*。  

 -  通用角色列表如下：  

    | 角色名 | 权限 | 等级 | 是否可以被赋予于节点 | 备注 |
    | --- | --- | --- | --- | --- |
    | 闲置人员 | 无 | 1 | 否 |  |
    | 执行者 | 改变节点状态 | 2 | 是 |  |
    | 节点管理员 | 改变节点状态、编辑节点 | 3 | 是 |  |
    | 树管理员 | 改变节点状态、编辑节点、对节点赋予角色 | 4 | 是 |  |
    | 项目管理员 | 改变节点状态、编辑节点、对节点赋予角色、编辑项目、修改通用角色 | 5 | 否 |  |
    | 项目创始人 | 所有权限 | 6 | 否 | 项目创始人只能有一个，且不能变更 |


 -  项目自定义角色是项目自定义且可修改的角色，其定义时需要接收一个用户等级。  
    该角色便会复制对应等级的通用角色的所有权限并合并新的权限，某种意义上相当于继承。

 -  涉及到角色等级比较的只有编辑角色的相关操作，其过程如下：
    - 若是修改通用角色操作，仅当操作者的项目角色等级 > 被操作者的项目角色等级时，操作才能有效。
    - 若是对节点赋予角色操作，仅当操作者对应节点的节点角色等级 > 被操作者对应节点的节点角色等级时，操作才能有效。

 -  当修改用户对应的项目角色时，会删除对应用户的所有节点角色记录。  
    当修改用户某节点对应的节点角色时，会删除该结点所有后代存在的对应用户的节点角色记录。

<br>

##  节点相关逻辑:

 -  项目新建后，生成一个 *parent_id = null，height = 1* 的根节点

 -  根节点仅在删除项目时才能被删除。

 -  判断该用户对应某节点的角色方法：
    - 包括该节点，一直向父节点方向遍历，直至找到第一个在 *node_user* 有对应记录的节点，其角色作为该节点对应的角色。
    - 若遍历到根节点仍未找到对应记录，则将通用角色作为该节点对应的角色。

 -  在 *node_user* 表中为用户对应的的节点角色，但不是所有用户在所有节点都有对应的记录。

 -  任何用户在从根节点开始的由上到下的分支通路中。  
    若有节点角色记录，则其对应等级一定是升序的，且所有节点角色的等级 > 该用户的通用角色等级。

 -  当给一个节点赋予角色时，会删除该节点所有后代节点的节点角色记录

<br>

##  测试数据说明

 -  测试用户如下，密码均为123：  

    | 邮箱 | 角色说明 | 备注 |
    | --- | --- | --- |
    | super@qq.com | 项目创始人 |  |
    | free@qq.com | 闲置人员 |  |
    | project@qq.com | 项目管理员 |  |
    | node_role_2@qq.com | 在2节点角色为执行者的闲置 | 仅在项目1有节点角色 |
    | node_role_3@qq.com | 在2节点角色为节点管理员的闲置 | 仅在项目1有节点角色 |
    | node_role_4@qq.com | 在2节点角色为树管理员的闲置 | 仅在项目1有节点角色 |
    | empty@qq.com | 无业人士 | 未加入任何项目 |



 -  初定权限列表如下：  

    | 路由 | 权限 | 所属分组id | 备注 |
    | --- | --- | --- | --- |
    | node.store.change_status | 改变节点状态 | 2 |  |
    | node.create | 新增节点 | 3 |  |
    | node.update | 编辑节点 | 3 |  |
    | node.delete | 删除节点 | 3 |  |
    | node_user.create | 新增节点角色 | 4 |  |
    | node_user.store | 编辑节点角色 | 4 |  |
    | node_user.delete | 移除节点角色 | 4 |  |
    | project.store | 编辑项目 | 5 |  |
    | project_user.create | 邀请新成员 | 5 |  |
    | project_user.store | 修改项目角色 | 5 |  |
    | project_user.delete | 移除成员 | 5 |  |
    | project.delete | 删除项目 | 6 |  |


