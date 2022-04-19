<?php


final class Listing
{
    private $db;

    public function __construct()
    {

        $config = include 'config.php';
        $this->db = new PDO($config['dsn'], $config['username'], $config['password']);

    }


    public function printGroupLists($groupId): string
    {
        $groupsItems = [];
        $this->getGroupItems($groupId, $groupsItems);
        $tree = $this->objectsToTree($groupsItems);

        return $this->getHtmlGroup($tree, $groupId);
    }

    private function getGroupItems($parentId, &$groupsItems): void
    {

        $command = "SELECT * from groups where groups.id_parent = $parentId";
        $groups = $this->db->query($command);
        foreach ($groups as $group) {
            $groupsItems[] = [
                'id' => $group['id'],
                'parent' => $group['id_parent'],
                'label' => $group['name']
            ];
        }
        $this->getParents($parentId, $groupsItems);

    }

    private function getParents($parentId, &$groupsItems): void
    {
        $command = "SELECT * from groups where groups.id = $parentId";
        $groups = $this->db->query($command);
        foreach ($groups as $group) {
            $this->getGroupItems($group['id_parent'], $groupsItems);
        }
    }

    private function objectsToTree(&$categories): array
    {

        $map = array(
            0 => array('subObjects' => array())
        );

        foreach ($categories as &$category) {
            $category['subObjects'] = array();
            $map[$category['id']] = &$category;
        }

        foreach ($categories as &$category) {
            $map[$category['parent']]['subObjects'][] = &$category;
        }

        return $map[0]['subObjects'];

    }

    private function getHtmlGroup($tree, $groupId): string
    {
        $html = "<ul>";

        foreach ($tree as $item) {
            $class = '';
            if ($item['id'] == $groupId) {
                $class = 'class="active"';
            }
            $count = $this->getProducts($this->getChildIds($item['id']), true);
            $html .= "<li $class><a href='?group={$item['id']}'>{$item['label']}</a> $count</li>";
            if (!empty($item['subObjects'])) {
                $html .= $this->getHtmlGroup($item['subObjects'], $groupId);
            }
        }

        $html .= "</ul>";

        return $html;
    }


    public function printProductsList($objectId)
    {
        $ids = $this->getChildIds($objectId);

        return $this->getProducts($ids);

    }

    private function getProducts($ids, $isCount = false)
    {
        $html = "";
        $command = "Select * from products where id_group in ($ids)";
        $products = $this->db->query($command);

        if ($isCount) {
            return $products->rowCount();
        }
        foreach ($products as $product) {
            $html .= "<div class='product'>{$product['name']}</div>";
        }

        if (empty($html)) {
            return "<div>В данной категории товаров нет</div>";
        }

        return $html;

    }

    private function getChildIds($objectId): string
    {
        $command = "SELECT GROUP_CONCAT(id) AS ids from 
                    (select * from groups order BY id_parent, id) groups_sorted, 
                    (select @pv := '$objectId') initialisation where find_in_set(id_parent, @pv) 
                    and length(@pv := concat(@pv, ',', id))";

        $ids = $this->db->query($command)->fetch();
        if (!empty($ids['ids'])) {
            return $ids['ids'] . ",$objectId";
        }
        return trim($objectId);

    }


}