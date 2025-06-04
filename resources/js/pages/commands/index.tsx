import { Head, usePage } from '@inertiajs/react';
import { Server } from '@/types/server';
import Container from '@/components/container';
import HeaderContainer from '@/components/header-container';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import ServerLayout from '@/layouts/server/layout';
import { PlusIcon } from 'lucide-react';
import { DataTable } from '@/components/data-table';
import { columns } from '@/pages/commands/components/columns';
import CreateCommand from '@/pages/commands/components/create-command';
import { PaginatedData } from '@/types';
import { Command } from '@/types/command';
import { Site } from '@/types/site';

export default function Commands() {
  const page = usePage<{
    server: Server;
    site: Site;
    commands: PaginatedData<Command>;
  }>();

  return (
    <ServerLayout>
      <Head title={`Commands - ${page.props.site.domain} - ${page.props.server.name}`} />

      <Container className="max-w-5xl">
        <HeaderContainer>
          <Heading title="Commands" description="These are the commands that you can run on your site's location" />
          <div className="flex items-center gap-2">
            <CreateCommand>
              <Button>
                <PlusIcon />
                <span className="hidden lg:block">Create</span>
              </Button>
            </CreateCommand>
          </div>
        </HeaderContainer>

        <DataTable columns={columns} paginatedData={page.props.commands} />
      </Container>
    </ServerLayout>
  );
}
