import SettingsLayout from '@/layouts/settings/layout';
import { Head, usePage } from '@inertiajs/react';
import { DataTable } from '@/components/data-table';
import { columns } from '@/pages/projects/components/columns';
import { Project } from '@/types/project';
import Container from '@/components/container';
import Heading from '@/components/heading';
import ProjectForm from '@/pages/projects/components/project-form';
import { Button } from '@/components/ui/button';
import { PaginatedData } from '@/types';

export default function Projects() {
  const page = usePage<{
    projects: PaginatedData<Project>;
  }>();

  return (
    <SettingsLayout>
      <Head title="Projects" />

      <Container className="max-w-5xl">
        <div className="flex items-start justify-between">
          <Heading title="Projects" description="Here you can manage your projects" />
          <div className="flex items-center gap-2">
            <ProjectForm>
              <Button>Create project</Button>
            </ProjectForm>
          </div>
        </div>
        <DataTable columns={columns} paginatedData={page.props.projects} />
      </Container>
    </SettingsLayout>
  );
}
