
[TOC]

----------


# 角色、权限相关逻辑:

 - 对于角色而言，角色分为 **全局角色** 与 **项目自定义角色**。
   **全局角色**：每个项目均存在且无法修改的角色。
   **项目自定义角色**：项目自定义且可修改的角色。
   两种角色对应不同的模型，使用多态关联实现其关联逻辑。

 - 对于项目而言，角色分为 **项目角色** 与 **节点角色**。
   **项目角色**： *project_user.role_id* 是用户在项目中的真实角色。
   **节点角色**： *node_user.role_id* 是涉及节点逻辑的对应角色。
   分成这两类角色的意义在于区分角色间的等级比较。

 - 全局角色列表如下：
| 角色名 | 权限 | 等级 |
| --- | --- | --- | --- |
| 闲置人员 | 无 | 1 |
| 执行者 | 改变节点状态 | 2 |
| 节点管理员 | 改变节点状态、编辑节点 | 3 |
| 树管理员 | 改变节点状态、编辑节点、对节点赋予角色 | 4 |
| 项目管理员 | 改变节点状态、编辑节点、对节点赋予角色、编辑项目、修改全局角色 | 5 |
| 超级管理员 | 所有权限 | 6 |


 - 项目自定义角色是项目自定义且可修改的角色，其定义时需要接收一个用户等级。
   该角色便会复制对应等级的全局角色的所有权限并合并新的权限，某种意义上相当于继承。
 
 - 涉及到角色等级比较的只有修改角色的操作，其过程如下：
    - 若是修改全局角色操作，仅当操作者的项目角色等级大于被操作者的项目角色等级时，操作才能有效。
    - 若是对节点赋予角色操作，则满足下面条件之一时，操作有效:
      - 操作者的项目角色等级 > 被操作者的项目角色等级
      - 操作者的项目角色等级 == 被操作者的项目角色等级 && 操作者的节点角色等级 > 被操作者的项目角色等级
 
----------

# 节点相关逻辑:

 - 项目新建后，生成一个 *parent_id = null，height = 1* 的根节点

 - 在 *node_user* 表中为用户对应的的节点角色，但不是所有用户在所有节点都有对应的记录。

 - 任何用户在从根节点开始的由上到下的分支通路中。
   若有节点角色记录，则其对应等级一定是升序的，且所有节点角色的等级均大于该用户的全局角色等级。

 - 若子树含有某等级节点角色，当给一个包含该子数根节点的更大的子树赋予同等级节点角色。
   则需要将小子树的 *node_user* 表中对应的记录修改为大子树的。

 - 判断该用户对应某节点的角色方法：
    - 包括该节点，一直向父节点方向遍历，直至找到第一个在 *node_user* 有对应记录的节点，其角色作为该节点对应的角色。
    - 若遍历到根节点仍未找到对应记录，则将全局角色作为该节点对应的角色。





    