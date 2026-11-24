export interface WikiPageLink {
  title: string;
  slug: string;
  updatedAt: string;
  // Category path, only set in search results
  path?: string | null;
}

export interface WikiCategoryNode {
  id: string;
  name: string;
  parentId: string | null;
  children: WikiCategoryNode[];
  pages: WikiPageLink[];
}

export interface WikiCategoryOption {
  id: string;
  path: string;
}

export interface WikiPage {
  id: string;
  slug: string;
  title: string;
  categoryId: string | null;
  pinned: boolean;
  createdAt: string;
  updatedAt: string;
  creator?: { id: number; name: string } | null;
  editor?: { id: number; name: string } | null;
}
