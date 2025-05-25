import SettingsLayout from '@/layouts/settings/layout';
import { Head, usePage } from '@inertiajs/react';
import Container from '@/components/container';
import Heading from '@/components/heading';
import { DataTable } from '@/components/data-table';
import { Tag } from '@/types/tag';
import { columns } from '@/pages/tags/components/columns';
import { Button } from '@/components/ui/button';
import CreateTag from '@/pages/tags/components/create-tag';
import { PaginatedData } from '@/types';

type Page = {
  tags: PaginatedData<Tag>;
};

export default function Tags() {
  const page = usePage<Page>();
  return (
    <SettingsLayout>
      <Head title="Tags" />
      <Container className="max-w-5xl">
        <div className="flex items-start justify-between">
          <Heading title="Tags" description="Here you can manage tags" />
          <div className="flex items-center gap-2">
            <CreateTag>
              <Button>Create</Button>
            </CreateTag>
          </div>
        </div>
        <DataTable columns={columns} paginatedData={page.props.tags} />
      </Container>
    </SettingsLayout>
  );
}
