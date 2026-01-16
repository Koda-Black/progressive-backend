<?php
/**
 * Menu Controller - Public endpoints
 */

declare(strict_types=1);

namespace ProgressiveBar\Controllers;

use ProgressiveBar\Core\Request;
use ProgressiveBar\Core\Response;
use ProgressiveBar\Models\MenuItem;

class MenuController
{
    /**
     * GET /api/menu
     * Fetch all menu items grouped by category
     */
    public function index(Request $request): void
    {
        $category = $request->getQuery('category');
        $search = $request->getQuery('search');
        
        if ($search) {
            $items = MenuItem::search($search);
        } elseif ($category) {
            $items = MenuItem::findAll(['category' => $category, 'available' => true]);
        } else {
            $items = MenuItem::findAll(['available' => true]);
        }
        
        // Get categories
        $categories = MenuItem::getCategories();
        
        Response::success([
            'categories' => $categories,
            'items' => $items,
        ]);
    }

    /**
     * GET /api/menu/{id}
     * Get a single menu item
     */
    public function show(Request $request, array $params): void
    {
        $id = $params['id'] ?? null;
        
        if (!$id) {
            Response::error('Item ID required', 400);
            return;
        }
        
        $item = MenuItem::findById($id);
        
        if (!$item) {
            Response::notFound('Menu item not found');
            return;
        }
        
        Response::success($item);
    }

    /**
     * POST /api/menu/availability
     * Check availability of multiple items
     */
    public function checkAvailability(Request $request): void
    {
        $itemIds = $request->getBody('itemIds');
        
        if (!is_array($itemIds) || empty($itemIds)) {
            Response::error('Item IDs array required', 400);
            return;
        }
        
        $items = MenuItem::findByIds($itemIds);
        $availability = [];
        
        foreach ($items as $item) {
            $availability[$item['id']] = $item['available'];
        }
        
        Response::success($availability);
    }
}
