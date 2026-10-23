@include('user.layouts.header')
@include('user.layouts.back-nav')
@include('user.layouts.search')

<section class="w-full md:w-3/6 bg-white mx-auto p-3 text-sm pb-20">
    <div class="border-b-2 border-b-gray-200 pt-4 px-2 font-bold text-dark_green mb-2">
        Get Trusted, Get Verified
        @include('public.components.flash-message')
    </div>
  
		            
		        	<div class="max-w-2xl mx-auto bg-white p-3 rounded-lg">
				        <form method="POST" action="/user/submit-verification" enctype="multipart/form-data">
				            @csrf
				       
				        <div class="mb-4 mt-1">



				        @if($user->acc_type ==="Commercial")
				        <div class="mb-4 mt-4">
				            <label class="text-sm font-semibold mb-2">CAC Number *</label>
				            @if ($errors->has('document_number'))
				                <span class="text-red-700 py-1">{{ $errors->first('document_number') }}</span>
				            @endif
				            <input type="text"  name="document_number" placeholder="CAC Number" class="w-full px-3 py-2 border border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
				        </div>

				        <div class="mb-6 mt-4">
				           <label for="profile_image" class="block text-gray-700 text-sm font-bold mb-2">Upload CAC Document</label>
				           @if ($errors->has('cac_document_file'))
				                <span class="text-red-900 my-1">{{ $errors->first('cac_document_file') }}</span>
				            @endif
						    <input type="file" name="document_file" id="document_file"
						        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
				         </div>

				         <div class="mb-6 mt-4">
				           <label for="proof_address" class="block text-gray-700 text-sm font-bold mb-2">Proof of Address:  <small>Utility Bills (Electricity, Water, Waste), rent receipt or tenancy agreement</small> </label>
				           @if ($errors->has('proof_address'))
				                <span class="text-red-900 my-1">{{ $errors->first('proof_address') }}</span>
				            @endif
						    <input type="file" name="proof_address" id="proof_address"
						        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
				         </div>
				        @else

				        <div class="mb-4 mt-4">
				            <label class="text-sm font-semibold mb-2">Document Number <small>(NIN No, Passport No, Drivers License No)</small> *</label>
				            @if ($errors->has('document_number'))
				                <span class="text-red-700 py-1">{{ $errors->first('document_number') }}</span>
				            @endif
				            <input type="text"  name="document_number" placeholder="Document Number" class="w-full px-3 py-2 border border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
				        </div>

				        <div class="mb-4 mt-4">
				            <label class="text-sm font-semibold mb-2">Document Type *</label>
				            @if ($errors->has('document_type'))
				                <span class="text-red-700 py-1">{{ $errors->first('document_type') }}</span>
				            @endif
							<select name="document_type" id="document_type" class="w-full bg-body-100 px-3 py-2 border border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
								<option value="">--Select Option--</option>
								<option value="Drivers License">Drivers License</option>
								<option value="Passport">Passport</option>
								<option value="NIN">NIN</option>
								<option value="Passport">Passport</option>
								<option value="Voters Registeration">Voters Registration</option>
							</select>
				        </div>

				        <div class="mb-6 mt-4">

				           <label for="profile_image" class="block text-gray-700 text-sm font-bold mb-2">Upload Document</label>
				           @if ($errors->has('document_file'))
				                <span class="text-red-900 my-1">{{ $errors->first('document_file') }}</span>
				            @endif
						    <input type="file" name="document_file" id="document_file"
						        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
				         </div>


				        @endif
				        


				        @if($user->verified=="no")
				          <div class="mt-8">
				            <button type="submit" class="btn btn-primary py-1 text-lg flex justify-center items-center">
				                <span>Submit</span>
				                <span class="ml-2">
				                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
				                      <path fill-rule="evenodd" d="M16.72 7.72a.75.75 0 0 1 1.06 0l3.75 3.75a.75.75 0 0 1 0 1.06l-3.75 3.75a.75.75 0 1 1-1.06-1.06l2.47-2.47H3a.75.75 0 0 1 0-1.5h16.19l-2.47-2.47a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
				                    </svg>
				                </span>
				            </button>
				          </div>
				         @else
				        <span class=" bg-dark_green p-4 text-white mt-10 rounded font-semibold flex gap-2 w-64">
				        	<span>
				        		<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-check" viewBox="0 0 16 16">
								  <path d="M12.5 16a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7m1.679-4.493-1.335 2.226a.75.75 0 0 1-1.174.144l-.774-.773a.5.5 0 0 1 .708-.708l.547.548 1.17-1.951a.5.5 0 1 1 .858.514M11 5a3 3 0 1 1-6 0 3 3 0 0 1 6 0M8 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4"/>
								  <path d="M8.256 14a4.5 4.5 0 0 1-.229-1.004H3c.001-.246.154-.986.832-1.664C4.484 10.68 5.711 10 8 10q.39 0 .74.025c.226-.341.496-.65.804-.918Q8.844 9.002 8 9c-5 0-6 3-6 4s1 1 1 1z"/>
								</svg>
				        	</span>
				        	<span>Account is Now Verified</span>
				        </span>

				         @endif
				     				        
				        </form>
				        
				    </div>
		
 
</div>

    
</section>



@include('user.layouts.footer')
